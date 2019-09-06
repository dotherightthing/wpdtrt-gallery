/**
 * File: cypress/integration/flows/wpdtrt-gallery.js
 *
 * Dummy Cypress spec for UI testing. Edit to suit your needs.
 *
 * Since:
 *   0.8.13 - DTRT WordPress Plugin Boilerplate Generator
 *
 * See:
 * - [Testing & Debugging](https://github.com/dotherightthing/wpdtrt-plugin-boilerplate/wiki/Testing-&-Debugging)
 */

/* eslint-disable prefer-arrow-callback */
/* eslint-disable max-len */

// Test principles:
// ARRANGE: SET UP APP STATE > ACT: INTERACT WITH IT > ASSERT: MAKE ASSERTIONS

// Aliases are cleared between tests
// https://stackoverflow.com/questions/49431483/using-aliases-in-cypress

// Passing arrow functions (“lambdas”) to Mocha is discouraged
// https://mochajs.org/#arrow-functions
/* eslint-disable func-names */

const componentId = 'signposts';

describe( 'Test Name', function () {
  before( function () {
    // load local web page
    cy.visit( '/path/to/page' );
  } );

  beforeEach( function () {
    // refresh the page to reset the UI state
    cy.reload();

    // @aliases
    cy.get( `#${ componentId }` ).as( 'componentName' );
    cy.get( `#${ componentId } .child` ).as( 'componentChildName' );

    // default viewer attributes
    cy.get( '@componentChildName' )
      .should( 'have.attr', 'id', 'foo' )
      .should( 'have.attr', 'data-bar', 'false' )
      .should( 'not.have.attr', 'data-baz' );

    // scroll component into view,
    // as Cypress can't always 'see' elements below the fold
    cy.get( '@componentName' )
      .scrollIntoView( {
        offset: {
          top: 100,
          left: 0
        }
      } )
      .should( 'be.visible' );

    // @aliases for injected elements
    cy.get( `#${ componentId } .child-injected` ).as( 'componentInjected' );
  } );

  describe( 'Setup', function () {
    it( 'Has prerequisites', function () {
      // check that the plugin object is available
      cy.window().should( 'have.property', 'wpdtrt_gallery_ui' );

      // check that it's an object
      cy.window().then( ( win ) => {
        expect( win.wpdtrt_gallery_ui ).to.be.a( 'object' );
      } );
    } );
  } );

  describe( 'Load', function () {
    it( 'Loads', function () {
      // check that the child component has been assigned the correct ID
      cy.get( '@componentChildName' )
        .should( 'have.attr', 'id', `${ componentId }-child` )
        .should( 'have.attr', 'data-bar', 'true' )
        .should( 'not.have.attr', 'data-baz' );

      // check that the injected child component has the correct attributes and text
      cy.get( '@componentInjected' )
        .should( 'have.attr', 'aria-controls', `${ componentId }-child` )
        .contains( 'Sweet child of mine' );

      // test the accessibility of the component state using Tenon.io
      // Note: add a wrapper around the component so that the HTML can be submitted independently
      // and in its entirety
      cy.get( '@componentName' ).then( ( componentName ) => {
        // testing the contents rather than the length gives a more useful error object
        cy.task( 'tenonAnalyzeHtml', `${componentName.html()}` )
          // an empty resultSet indicates that there are no errors
          .its( 'resultSet' ).should( 'be.empty' );
      } );
    } );
  } );
} );
