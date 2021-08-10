define([], function () {
    'use strict';

    return {
        cache: [],

        getCacheId: function (key) {
            return JSON.stringify(key);
        },

        getData: function (key) {
            let cacheId = this.getCacheId(key);
            return this.cache[cacheId];
        },

        setData: function (key, data) {
            let cacheId = this.getCacheId(key);
            this.cache[cacheId] = data;
        }

    };
});
