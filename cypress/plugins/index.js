/**
 * @file cypress/plugins/index.js
 *
 * Custom plugins for UI testing
 *
 * Since:
 *   0.8.13 - DTRT WordPress Plugin Boilerplate Generator
 */

/* eslint-env node */
/* globals Promise */

const normalizeWhitespace = require('normalize-html-whitespace');
const TenonNode = require('tenon-node');

// export a function
module.exports = (on) => {
    // configure plugins here
    on('task', {
    /**
     * @function
     * @summary Lint a URL in Tenon
     *
     * @param {string} url - Fully qualified URL
     *
     * @returns {object} Tenon response object
     * {@link https://github.com/poorgeek/tenon-selenium-example/blob/master/test/helpers/tenonCommands.js|tenonCommands}
     * {@link https://www.npmjs.com/package/tenon-node|tenon-node}
     * {@link https://github.com/dotherightthing/wpdtrt-plugin-boilerplate/wiki/Testing-&-Debugging#d-cypressio-front-end-unit-tests|Cypress.io: Front End Unit Tests}
     */
        tenonAnalyzeUrl(url) {
            const tenonApi = new TenonNode({
                key: process.env.TENON_API_KEY
            });

            return new Promise((resolve, reject) => {
                tenonApi.analyze(url, (err, tenonResult) => {
                    if (err) {
                        reject(err);
                    }

                    if (tenonResult.status > 400) {
                        reject(tenonResult.info);
                    } else {
                        resolve(tenonResult);
                    }
                });
            });
        },

        /**
         * @function
         * @summary Lint an HTML fragment in Tenon
         * @param {string} selectorHtml - HTML fragment
         * @returns {object} Tenon response object
         * {@link https://github.com/poorgeek/tenon-selenium-example/blob/master/test/helpers/tenonCommands.js|tenonCommands}
         * {@link https://www.npmjs.com/package/tenon-node|tenon-node}
         * {@link https://github.com/dotherightthing/wpdtrt-plugin-boilerplate/wiki/Testing-&-Debugging#d-cypressio-front-end-unit-tests|Cypress.io Front End Unit Tests}
         * @example
         * cy.task('tenonAnalyzeHtml', `${myElement.wrap('<div/>').parent().html()}`)
         *    .its('results').should('eq', []);
         */
        tenonAnalyzeHtml(selectorHtml) {
            const html = normalizeWhitespace(selectorHtml); // strip whitespace between html tags
            const tenonApi = new TenonNode({
                key: process.env.TENON_API_KEY
            });

            return new Promise((resolve, reject) => {
                tenonApi.analyze(html, { fragment: '1', level: 'AAA' }, (err, tenonResponse) => {
                    if (err) {
                        reject(new Error(err));
                    }

                    if (tenonResponse.status === 401) {
                        reject(new Error(`${tenonResponse.status} Authentication Error. Please check your API key.`));
                    } else if (tenonResponse.status > 400) {
                        reject(new Error(`${tenonResponse.status} Error`));
                    } else {
                        const dtrtResponse = {};
                        // list the issues
                        dtrtResponse.results = tenonResponse.resultSet.map(result => {
                            return `${ result.errorTitle }`;
                        });

                        resolve(dtrtResponse);
                    }
                });
            });
        }
    });
};
