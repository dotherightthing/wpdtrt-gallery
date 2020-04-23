/**
 * @file cypress/support/commands.js
 * @summary Custom commands for UI testing
 * @requires DTRT WordPress Plugin Boilerplate Generator 0.8.13
 */

/**
 * reset the page & UI state
 */
Cypress.Commands.add('resetUI', () => {
    // cy.reload(); // causes cypress to hang

    // set to size of macbook air 11" (dev machine)
    cy.viewport(1366, 768);

    cy.scrollTo(0, 0);
});
