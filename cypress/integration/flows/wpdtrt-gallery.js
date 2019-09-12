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

const componentClass = 'wpdtrt-gallery';

describe( 'Load', function () {
  before( function () {
    // load local web page
    cy.visit( '/tourdiaries/asia/east-asia/china-1/1/newzealand-to-china/' );
  } );

  beforeEach( function () {
    // refresh the page to reset the UI state
    cy.reload();

    // @aliases
    cy.get( `.${ componentClass }` ).eq( 0 ).as( 'galleryViewerBelowFold' );

    // default viewer attributes
    // when element is offscreen
    cy.get( '@galleryViewerBelowFold' )
      .should( 'have.attr', 'class', 'wpdtrt-gallery stack stack_link_viewer gallery-viewer h2-viewer' )
      .should( 'have.attr', 'data-has-gallery', 'false' )
      .should( 'have.attr', 'data-expanded', 'false' );

    // @aliases for injected elements
    // cy.get( `#${ componentId } .child-injected` ).as( 'componentInjected' );
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

  describe( 'Transform', function () {
    
    it( 'Transforms', function () {
      // scroll component into view,
      // as Cypress can't always 'see' elements below the fold
      cy.get( '@galleryViewerBelowFold' )
        .scrollIntoView( {
          offset: {
            top: 100,
            left: 0
          }
        } )
        .should( 'be.visible' );

      // check that the default markup has been transformed
      cy.get( '@galleryViewerBelowFold' )
        .should( 'have.attr', 'class', 'wpdtrt-gallery stack stack_link_viewer gallery-viewer h2-viewer' )
        .should( 'have.attr', 'data-has-gallery', 'true' )
        .should( 'have.attr', 'data-expanded', 'true' )
        .should( 'have.attr', 'data-expanded-contenttype', 'true' )
        .should( 'have.attr', 'data-panorama', '1' );
    } );
  } );

  // moved into its own test, else
  // "Because this error occurred during a 'before each' hook
  // we are skipping the remaining tests in the current suite: 'Load' "
  describe( 'Accessibility', function () {
    it( 'Validates', function () {
      // test the accessibility of the component state using Tenon.io
      // and in its entirety
      cy.get( '@galleryViewerBelowFold' ).then( ( galleryViewerBelowFold ) => {
        // Add a wrapper around the component so that the HTML can be submitted independently
        // testing the contents rather than the length gives a more useful error object
        cy.task( 'tenonAnalyzeHtml', `${galleryViewerBelowFold.wrap( '<div/>' ).parent().html()}` )
          .its( 'issueCounts' ).should( 'eq', {
            A: 0,
            AA: 0,
            AAA: 0
          } );
      } );
    } );
  } );
} );
