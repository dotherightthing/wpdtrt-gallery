/**
 * File: cypress/support/commands.js
 *
 * Custom commands for UI testing
 *
 * Since:
 *   0.8.13 - DTRT WordPress Plugin Boilerplate Generator
 */

import 'cypress-wait-until';
import './commands/wait-for-resources';

/**
 * reset the page & UI state
 */
Cypress.Commands.add( 'resetUI', () => {
  // cy.reload(); // causes cypress to hang

  // set to size of macbook air 11" (dev machine)
  cy.viewport( 1366, 768 );

  cy.scrollTo( 0, 0 );
} );
