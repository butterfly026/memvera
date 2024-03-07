import axios from 'axios';
export default {
    showMesasge(msg, type = 'error') {
        // if (messageInstance) {
        //     //如果有已经打开的弹框就先关闭
        //     // messageInstance.close();
        //     // messageInstance = null;
        // } else {
        //     messageInstance = ElementUI.Message({
        //         'showClose': true,
        //         'message': msg || i18n.$t('请求出错，请重试'),
        //         'type': type,
        //         'duration': 3000,
        //         'onClose': () => {
        //             messageInstance = null;
        //         }

        //     });
        // }
    },
    // post
    post(url, data, isLoading = false) {
        let requestUrl = route(url);
        return new Promise((resolve, reject) => {
            let req = {};
            let loading = {
                'configs': {
                    'isLoading': isLoading
                }
            };
            axios
                .post(requestUrl, req, loading)
                .then((response) => {
                    if (response) {
                        resolve(response.data);
                    }
                })
                .catch((err) => {
                    reject(err);
                });
        });
    },
    // get
    get(url, params, isLoading = false) {
        let paramsStr = '';
        if (params) {
            paramsStr = params;
        }
        let requestUrl = route(url) + paramsStr;
        let configs = {
            'isLoading': isLoading
        };

        return new Promise((resolve, reject) => {
            axios
                .get(requestUrl, {
                    'configs': configs
                })
                .then((response) => {
                    if (response.data) {
                        resolve(response.data);
                    }
                })
                .catch((err) => {
                    reject(err);
                });
        });
    },
    // patch
    patch(url, data) {
        let requestUrl = route(url);
        return new Promise((resolve, reject) => {
            let req = {};
            let configs = {
                'isLoading': isLoading
            };
            axios
                .patch(requestUrl, req, configs)
                .then((response) => {
                    resolve(response.data);
                })
                .catch((err) => {
                    reject(err);
                });
        });
    },
    //put
    put(url, data, isLoading = false) {
        let requestUrl = route(url);
        return new Promise((resolve, reject) => {
            let req = {};
            let loading = {
                'configs': {
                    'isLoading': isLoading
                }
            };
            axios
                .put(requestUrl, req, loading)
                .then((response) => {
                    resolve(response.data);
                })
                .catch((err) => {
                    reject(err);
                });
        });
    },
    //putUrl  将请求参数拼接在URL中
    putUrl(url, data, isLoading = false) {
        let requestUrl = route(url);
        return new Promise((resolve, reject) => {
            let req = data || {};
            let configs = {
                'isLoading': isLoading
            };

            axios
                .put(requestUrl, null, {
                    'params': req,
                    configs
                })
                .then((response) => {
                    resolve(response.data);
                })
                .catch((err) => {
                    reject(err);
                });
        });
    },
    //delete
    delete(url, params, isLoading = false) {
        let paramsStr = '';
        if (params) {
            paramsStr = params;
        }
        let requestUrl = route(url) + paramsStr;
        let configs = {
            'isLoading': isLoading
        };
        return new Promise((resolve, reject) => {
            axios
                .delete(requestUrl, {
                    'configs': configs
                })
                .then((response) => {
                    resolve(response.data);
                })
                .catch((err) => {
                    reject(err);
                });
        });
    },
    //deleteArray
    deleteArray(url, params, isLoading = false) {
        let requestUrl = route(url);
        return new Promise((resolve, reject) => {
            let req = {};

            let configs = {
                'isLoading': isLoading
            };
            axios
                .delete(requestUrl, {
                    'data': req,
                    configs
                })
                .then((response) => {
                    resolve(response.data);
                })
                .catch((err) => {
                    reject(err);
                });
        });
    },
    pnPost(url, data, isLoading = false, cb) {
        let requestUrl = route(url);
        let req = {};
        let loading = {
            'configs': {
                'isLoading': isLoading
            }
        };
        axios
            .post(requestUrl, req, loading)
            .then((res) => {
                if (res) {
                    cb(res);
                }
            })
            .catch((err) => {
                reject(err);
            });
    }
};
