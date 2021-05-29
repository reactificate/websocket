window.Reactificate = {};


/**
 * @package Reactificate Event Emitter
 * @constructor
 */
Reactificate.EventEmitter = function () {
    let events = {
        'on': {},
        'once': {}
    };

    this.on = function (name, listener) {
        if (!events['on'][name]) {
            events['on'][name] = [];
        }

        events['on'][name].push(listener);
    };

    this.once = function (name, listener) {
        if (!events['once'][name]) {
            events['once'][name] = [];
        }

        events['once'][name].push(listener);
    };

    this.dispatch = function (name, data = []) {
        let regularEvent = events['on'];
        if (regularEvent.hasOwnProperty(name)) {
            regularEvent[name].forEach(function (listener) {
                listener(...data)
            });
        }

        let onceEvent = events['once'];
        if (onceEvent.hasOwnProperty(name)) {
            onceEvent[name].forEach(function (listener) {
                listener(data);
            });

            delete onceEvent[name];
        }
    }
};

/**
 * @package Reactificate Websocket client
 * @param wsUri {string} An address to websocket server.
 * @param options {string|string[]} Additional websocket options
 * @constructor
 */
Reactificate.WSClient = (function () {
    function WSClient(wsUri, options = []) {

        let _this = this;
        let _event = new Reactificate.EventEmitter();
        /**@returns WebSocket**/
        let websocket;
        let reconnectionInterval = 100;
        let connectionState = 'standby';
        let willReconnect = true;

        /**
         * Log message to console
         * @param message
         */
        let log = function (message) {
            console.log(message);
        };

        let createSocket = function (isReconnecting = false) {
            if (true === isReconnecting) {
                connectionState = 'reconnecting';
                _event.dispatch('reconnecting');
            } else {
                connectionState = 'connecting';
                _event.dispatch('connecting');

            }

            if (wsUri.indexOf('ws://') === -1) {
                wsUri = 'ws://' + window.location.host + wsUri;

            }

            websocket = new WebSocket(wsUri, options);

            websocket.addEventListener('open', function (...arguments) {
                changeState('open', arguments);
            });

            websocket.addEventListener('message', function (...arguments) {
                _event.dispatch('message', arguments);
            });

            websocket.addEventListener('close', function (...arguments) {
                changeState('close', arguments);
            });

            websocket.addEventListener('error', function (...arguments) {
                changeState('error', arguments);
            });
        };

        let changeState = function (stateName, event) {
            connectionState = stateName;

            if ('close' === stateName) {
                if (willReconnect) {
                    _this.reconnect();
                }
            }

            _event.dispatch(stateName, [event]);
        };

        /**
         * Check if connection is opened
         * @returns {boolean}
         */
        this.isOpened = function () {
            return 'open' === connectionState;
        };

        /**
         * Gets server connection state
         * @returns {string}
         */
        this.getState = function () {
            return connectionState;
        };

        /**
         * Get browser implementation of WebSocket object
         * @return {WebSocket}
         */
        this.getWebSocket = () => websocket;

        /**
         * This event fires when a connection is opened/created
         * @param listener
         */
        this.onOpen = (listener) => _event.on('open', listener);

        /**
         * This event fires when message is received
         * @param listener
         */
        this.onMessage = (listener) => _event.on('message', listener);

        /**
         * Listens to Reactificate socket command
         * @param listener
         */
        this.onCommand = (listener) => _event.on('command', listener);

        /**
         * This event fires when this connection is closed
         * @param listener
         */
        this.onClose = (listener) => _event.on('close', listener);

        /**
         * This event fires when an error occurred
         * @param listener
         */
        this.onError = (listener) => _event.on('error', listener);

        /**
         * This event fires when this connection is in connecting state
         * @param listener
         */
        this.onConnecting = (listener) => _event.on('connecting', listener);

        /**
         * This event fires when this reconnection is in connecting state
         * @param listener
         */
        this.onReconnecting = (listener) => _event.on('reconnecting', listener);

        this.onReady = function (listener) {
            window.addEventListener('DOMContentLoaded', listener)
        };

        /**
         * Set reconnection interval
         * @param interval
         */
        this.setReconnectionInterval = function (interval) {
            reconnectionInterval = interval;
        };

        /**
         * Send message to websocket server
         * @param command {string} command name
         * @param message {array|object|int|float|string} message
         */
        this.send = function (command, message = {}) {
            if ('object' === typeof command) {  //when array|object is passed to command
                if (!Array.isArray(command)) {
                    command.time = new Date().getTime();
                }

                command = JSON.stringify(command);

            } else {    // when string is passed to command
                command = JSON.stringify({
                    command: command,
                    message: message,
                    time: new Date().getTime()
                });
            }

            //Send message
            return new Promise((resolve, reject) => {
                //Only send message when client is connected
                if (this.isOpened()) {
                    try {
                        websocket.send(command);
                        resolve(_this);
                    } catch (error) {
                        reject(error);
                    }

                    //Send message when connection is recovered
                } else {
                    log('Your message will be sent when server connection is recovered!');
                    _event.once('open', () => {
                        try {
                            websocket.send(command);
                            resolve(_this);
                        } catch (error) {
                            reject(error);
                        }
                    });
                }
            })
        };

        /**
         * Manually reconnect this connection
         */
        this.reconnect = function () {
            connectionState = 'internal_reconnection';
            this.close();

            if (false !== reconnectionInterval) {
                setTimeout(() => createSocket(true), reconnectionInterval);
            }
        };

        /**
         * Close this connection, the connection will not be reconnected.
         */
        this.close = function () {
            if ('internal_reconnection' === connectionState) {
                willReconnect = true;
            }

            websocket.close();
        };

        //CREATE SOCKET CONNECTION WHEN DOM FINISHED LOADING
        _this.onReady(function () {
            setTimeout(() => createSocket(), 100);
            //Notification handler
            _this.onMessage(function (payload) {
                payload = JSON.parse(payload.data);

                if (payload.command) {
                    //Dispatch command events
                    _event.dispatch('command', [payload]);

                    if ('reactificate.notification' === payload.command) {
                        let notif = new Reactificate.Notification();
                        notif.send({
                            title: payload.data.title,
                            body: payload.data.body
                        });
                    }
                }
            });
        });
    }

    return WSClient;
})();


/**
 * @package Reactificate Notification
 * @param wsUri {string} An address to websocket server.
 * @param options {string|string[]} Additional websocket options
 * @constructor
 */
Reactificate.Notification = (function () {
    function RNotification() {
        let _this = this;
        let _notification;

        this.request = function () {
            if (_this.isDeclined()) {
                return new Promise(function (resolve, reject) {
                    reject();
                });
            }

            return Notification.requestPermission();
        };

        this.send = function (object) {
            _notification = new Notification(object.title, object);
        }

        this.getNotification = () => _notification;

        this.isDefault = function () {
            return 'default' === RNotification.permission;
        };

        this.isGranted = function () {
            return 'granted' === RNotification.permission;
        };

        this.isDeclined = function () {
            return 'declined' === RNotification.permission;
        };


        setTimeout(() => _this.request(), 50);
    }


    return RNotification;
})();