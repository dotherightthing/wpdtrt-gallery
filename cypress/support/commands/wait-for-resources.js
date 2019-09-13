/**
 * File: cypress/support/commands/wait-for-resources.js
 *
 * Custom commands for UI testing
 *
 * See:
 * - <https://github.com/cypress-io/cypress-example-recipes/blob/master/examples/testing-dom__wait-for-resource/cypress/support/index.js>
 * - <https://github.com/cypress-io/cypress-example-recipes/tree/master/examples/testing-dom__wait-for-resource>
 *
 * Since:
 *   0.8.13 - DTRT WordPress Plugin Boilerplate Generator
 */


const { isPlainObject, last } = Cypress._;

/**
 * Adds command "cy.waitForResources(name1, name2, ...)"
 * that checks performance entries for resources that end with the given names.
 * This command will be available in every spec file.
 *
 * @example cy.waitForResources( 'base.css', 'app.css' )
 *
 * You can pass additional options, like "timeout"
 *
 * @example cy.waitForResources( 'base.css', 'app.css', { timeout: 3000 })
 */
Cypress.Commands.add( 'waitForResources', ( ...args ) => {
  let names;
  let options;

  if ( isPlainObject( last( args ) ) ) {
    names = args.slice( 0, args.length - 1 );
    options = last( args );
  } else {
    names = args;
    options = {};
  }

  const log = false; // let's not log inner commands
  const timeout = options.timeout || Cypress.config( 'defaultCommandTimeout' );

  cy.log( `Waiting for resources ${ names.join( ', ' ) }` );

  cy.window( { log } ).then(
    // note that ".then" method has options first, callback second
    // https://on.cypress.io/then
    { log, timeout },
    ( win ) => {
      return new Cypress.Promise( ( resolve, reject ) => {
        // flag set when we find all names
        let foundResources;

        // control how long we should try finding the resource
        // and if it is still not found. An explicit "reject"
        // allows us to show nice informative message
        setTimeout( () => {
          if ( foundResources ) {
            // nothing needs to be done, successfully found the resource
            return;
          }

          clearInterval( interval ); // eslint-disable-line no-use-before-define
          reject(
            new Error( `Timed out waiting for resources ${names.join( ', ' )}` )
          );
        }, timeout );

        const interval = setInterval( () => {
          foundResources = names.every( ( name ) => win.performance
            .getEntriesByType( 'resource' )
            .find( ( item ) => item.name.endsWith( name ) ) );
          if ( !foundResources ) {
            // some resource not found, will try again
            return;
          }

          cy.log( 'Found all resources' );
          clearInterval( interval );
          resolve();
        }, 100 );
      } );
    }
  );
} );
