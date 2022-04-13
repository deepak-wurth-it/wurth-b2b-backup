/* jscs:disable */
/* eslint-disable */

/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

var path = require('path');

/**
 * Convert the relative path imports within TypeScript into absolute paths with pre-fixed module name
 *
 * @returns {{visitor: {ImportDeclaration: visitor.ImportDeclaration}}}
 */
module.exports = function () {
    'use strict';

    return {
        visitor: {
            /**
             * Convert ../../utils/util import into Magento_Module/js/utils/util
             *
             * @param {Object} importPath
             * @param {Object} state
             * @constructor
             */
            ImportDeclaration: function (importPath, state) {
                console.log(importPath.node.source.value)
                var importExpression = importPath.node.source.value;

                if (!state.opts.prefix) {
                    throw Error('Prefix must be defined');
                }

                // Is the file being imported from another directory?
                if (!path.isAbsolute(importExpression) && importExpression.includes('./')) {
                    console.log(state.file.opts)
                    importPath.node.source.value = path.resolve(
                        path.dirname(state.file.opts.filename),
                        importExpression
                    ).replace(
                        process.cwd() + "/src",
                        state.opts.prefix.replace(/\/+$/, '') + "/js"
                    );
                }
            }
        }
    };
};
