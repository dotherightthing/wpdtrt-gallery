/**
 * File: cypress/plugins/index.js
 *
 * Custom plugins for UI testing
 *
 * Since:
 *   0.8.13 - DTRT WordPress Plugin Boilerplate Generator
 */

/* eslint-env node */
/* globals Promise */

const normalizeWhitespace = require( 'normalize-html-whitespace' );
const TenonNode = require( 'tenon-node' );

// export a function
module.exports = ( on ) => {
  // configure plugins here
  on( 'task', {
    /**
     * Lint a URL in Tenon
     *
     * Parameters:
     *   (string) url - Fully qualified URL
     *
     * Returns:
     *   (object) - Tenon response object
     *
     * See:
     * - <tenonCommands: https://github.com/poorgeek/tenon-selenium-example/blob/master/test/helpers/tenonCommands.js>
     * - <tenon-node: https://www.npmjs.com/package/tenon-node>
     * - <Cypress.io Front End Unit Tests: https://github.com/dotherightthing/wpdtrt-plugin-boilerplate/wiki/Testing-&-Debugging#d-cypressio-front-end-unit-tests>
     */
    tenonAnalyzeUrl( url ) {
      const tenonApi = new TenonNode( {
        key: process.env.TENON_API_KEY
      } );

      return new Promise( ( resolve, reject ) => {
        tenonApi.analyze( url, ( err, tenonResult ) => {
          if ( err ) {
            reject( err );
          }

          if ( tenonResult.status > 400 ) {
            reject( tenonResult.info );
          } else {
            resolve( tenonResult );
          }
        } );
      } );
    },

    /**
     * Lint an HTML fragment in Tenon
     *
     * Parameters:
     *   (string) selectorHtml - HTML fragment
     *
     * Returns:
     *   (object) - Tenon response object
     *
     * See:
     * - <tenonCommands: https://github.com/poorgeek/tenon-selenium-example/blob/master/test/helpers/tenonCommands.js>
     * - <tenon-node: https://www.npmjs.com/package/tenon-node>
     * - <Cypress.io Front End Unit Tests: https://github.com/dotherightthing/wpdtrt-plugin-boilerplate/wiki/Testing-&-Debugging#d-cypressio-front-end-unit-tests>
     *
     * Example:
     * --- js
     * cy.task( 'tenonAnalyzeHtml', `${myElement.wrap( '<div/>' ).parent().html()}` )
     *    .its( 'results' ).should( 'eq', [] );
     * ---
     */
    tenonAnalyzeHtml( selectorHtml ) {
      const html = normalizeWhitespace( selectorHtml ); // strip whitespace between html tags
      const tenonApi = new TenonNode( {
        key: process.env.TENON_API_KEY
      } );

      return new Promise( ( resolve, reject ) => {
        tenonApi.analyze( html, { fragment: '1', level: 'AAA' }, ( err, tenonResponse ) => {
          if ( err ) {
            reject( new Error( err ) );
          }

          if ( tenonResponse.status === 401 ) {
            reject( new Error( `${tenonResponse.status} Authentication Error. Please check your API key.` ) );
          } else if ( tenonResponse.status > 400 ) {
            reject( new Error( `${tenonResponse.status} Error` ) );
          } else {
            const dtrtResponse = {};
            // list the issues
            dtrtResponse.results = tenonResponse.resultSet.map( result => {
              return `${ result.errorTitle }`;
            } );

            resolve( dtrtResponse );
          }
        } );
      } );
    }
  } );
};
