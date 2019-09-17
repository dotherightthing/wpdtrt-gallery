/**
 * File: cypress/integration/flows/wpdtrt-gallery--image.js
 *
 * Cypress spec for End-to-End UI testing.
 *
 * Since:
 *   0.8.13 - DTRT WordPress Plugin Boilerplate Generator
 *
 * See:
 * - <Testing & Debugging: Cypress.io: https://github.com/dotherightthing/wpdtrt-plugin-boilerplate/wiki/Testing-&-Debugging:-Cypress.io>
 *
 * TODO:
 * - test user selected default image (if not first in set)
 */

/* eslint-disable prefer-arrow-callback */
/* eslint-disable max-len */

// Test principles:
// ARRANGE: SET UP APP STATE > ACT: INTERACT WITH IT > ASSERT: MAKE ASSERTIONS

// Passing arrow functions (“lambdas”) to Mocha is discouraged
// https://mochajs.org/#arrow-functions
/* eslint-disable func-names */

const componentClass = 'wpdtrt-gallery-viewer';
const sectionId = 'itchy-feet';

describe( 'DTRT Gallery - Image Gallery Item', function () {
  before( function () {
    // load local tour diary page
    cy.visit( '/tourdiaries/asia/east-asia/china-1/1/newzealand-to-china/' );
  } );

  beforeEach( function () {
    // @aliases
    // Aliases are cleared between tests
    // https://stackoverflow.com/questions/49431483/using-aliases-in-cypress

    // a gallery viewer that is below the fold won't have been transformed yet
    // the default gallery item is an image, which is accessible
    cy.get( `#${ sectionId } .${ componentClass } + .gallery` )
      .as( 'wpdtrtGallery' );

    cy.get( '@wpdtrtGallery' ).find( '.gallery-item' ).eq( 0 )
      .as( 'wpdtrtGalleryFirstItem' );
  } );

  describe( 'A. Configuration', function () {
    it( 'Plugin UI object exists', function () {
      cy.resetUI();

      // check that the plugin object is available
      cy.window()
        .should( 'have.property', 'wpdtrt_gallery_ui' );

      // check that it's an object
      cy.window().then( ( win ) => {
        expect( win.wpdtrt_gallery_ui ).to.be.a( 'object' );
      } );
    } );
  } );

  // default gallery attributes
  // when element is offscreen
  describe( 'B. First item - default state', function () {
    it( '1. Does contain a link to an enlargement, with no other attributes', function () {
      cy.get( '@wpdtrtGalleryFirstItem' ).find( 'a' ).as( 'wpdtrtGalleryFirstLink' );

      cy.get( '@wpdtrtGalleryFirstLink' )
        .should( 'exist' );

      // TODO
      // "In most cases, .should() yields the same subject it was given
      // from the previous command."
      // but here it results in
      // "expected undefined not to have attribute data-id"

      cy.get( '@wpdtrtGalleryFirstLink' )
        .should( 'not.have.attr', 'aria-controls' );

      cy.get( '@wpdtrtGalleryFirstLink' )
        .should( 'not.have.attr', 'data-id' );

      cy.get( '@wpdtrtGalleryFirstLink' )
        .should( 'not.have.attr', 'data-src-desktop-expanded' );

      cy.get( '@wpdtrtGalleryFirstLink' )
        .should( 'not.have.attr', 'class' );

      cy.get( '@wpdtrtGalleryFirstLink' )
        .invoke( 'attr', 'href' )
        .should( 'match', /wpsize=wpdtrt-gallery-panorama/ );
    } );

    it( '2. Does contain a square thumbnail, described accessibly', function () {
      cy.get( '@wpdtrtGalleryFirstItem' ).find( 'img' ).as( 'wpdtrtGalleryFirstImage' );

      cy.get( '@wpdtrtGalleryFirstImage' )
        .should( 'exist' )
        .should( 'have.css', 'width', '104px' ) // 300px scaled
        .should( 'have.css', 'height', '104px' ); // 300px scaled

      cy.get( '@wpdtrtGalleryFirstImage' )
        .invoke( 'attr', 'alt' )
        .should( 'match', /.+/ ); // alt !== ''

      cy.get( '@wpdtrtGalleryFirstImage' )
        .invoke( 'attr', 'src' )
        .should( 'match', /wpsize=thumbnail/ );
    } );

    it( '3. Thumbnail is described by the adjacent caption', function () {
      const captionId = 'gallery-9-14808';
      cy.get( '@wpdtrtGalleryFirstItem' ).find( 'img' ).as( 'wpdtrtGalleryFirstImage' );

      cy.get( '@wpdtrtGalleryFirstImage' )
        .should( 'have.attr', 'aria-describedby', captionId );

      cy.get( '@wpdtrtGallery' ).find( `#${ captionId }` )
        .should( 'exist' );
    } );

    it( '4. Is a panorama', function () {
      cy.get( '@wpdtrtGalleryFirstItem' ).find( 'a' ).as( 'wpdtrtGalleryFirstLink' );
      cy.get( '@wpdtrtGalleryFirstItem' ).find( 'img' ).as( 'wpdtrtGalleryFirstImage' );

      cy.get( '@wpdtrtGalleryFirstLink' )
        .invoke( 'attr', 'href' )
        .should( 'match', /wpsize=wpdtrt-gallery-panorama/ );


      cy.get( '@wpdtrtGalleryFirstImage' )
        .should( 'have.attr', 'data-panorama', '1' );
    } );

    it( '5. Passes Tenon validation', function () {
      this.skip();

      cy.get( '@wpdtrtGalleryFirstItem' ).then( ( wpdtrtGalleryFirstItem ) => {
        // Add a wrapper around the component so that the HTML can be submitted independently
        cy.task( 'tenonAnalyzeHtml', `${wpdtrtGalleryFirstItem.wrap( '<div/>' ).parent().html()}` )
          .its( 'results' ).should( 'eq', [] );
      } );
    } );
  } );

  describe( 'C. First item - enhanced state', function () {
    it( '1. Above the fold', function () {
      cy.resetUI();

      // scroll component into view,
      // as Cypress can't always 'see' elements below the fold
      cy.get( '@wpdtrtGallery' )
        .scrollIntoView( {
          duration: 1500,
          offset: {
            top: 0,
            left: 0
          }
        } )
        .should( 'be.visible' );
    } );

    it( '2. Attributes transferred from thumbnail to wrapping link', function () {
      cy.get( '@wpdtrtGalleryFirstItem' ).find( 'a' ).as( 'wpdtrtGalleryFirstLink' );

      cy.get( '@wpdtrtGalleryFirstLink' )
        .should( 'exist' );

      // TODO
      // "In most cases, .should() yields the same subject it was given
      // from the previous command."
      // but not here..

      cy.get( '@wpdtrtGalleryFirstLink' )
        .invoke( 'attr', 'href' )
        .should( 'match', /wpsize=wpdtrt-gallery-panorama/ );

      cy.get( '@wpdtrtGalleryFirstLink' )
        .should( 'have.attr', 'data-panorama', '1' );

      cy.get( '@wpdtrtGalleryFirstLink' )
        .should( 'have.attr', 'data-src-desktop-expanded' );

      cy.get( '@wpdtrtGalleryFirstLink' )
        .invoke( 'attr', 'data-src-desktop-expanded' )
        .should( 'match', /wpsize=wpdtrt-gallery-desktop-expanded/ );

      cy.get( '@wpdtrtGalleryFirstLink' )
        .should( 'not.have.attr', 'class' );
    } );

    it( '3. Is selected, visually and accessibly', function () {
      cy.get( '@wpdtrtGalleryFirstItem' ).find( 'a' ).as( 'wpdtrtGalleryFirstLink' );

      cy.get( '@wpdtrtGalleryFirstLink' )
        .should( 'have.attr', 'aria-controls', `${sectionId}-viewer` );

      cy.get( '@wpdtrtGalleryFirstLink' )
        .should( 'have.attr', 'aria-expanded', 'true' );
    } );

    it( '4. Passes Tenon validation', function () {
      this.skip();

      cy.get( '@wpdtrtGalleryFirstItem' ).then( ( wpdtrtGalleryFirstItem ) => {
        // Add a wrapper around the component so that the HTML can be submitted independently
        cy.task( 'tenonAnalyzeHtml', `${wpdtrtGalleryFirstItem.wrap( '<div/>' ).parent().html()}` )
          .its( 'results' ).should( 'eq', [] );
      } );
    } );
  } );
} );
