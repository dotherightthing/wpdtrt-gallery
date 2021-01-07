/**
 * @file cypress/integration/flows/wpdtrt-gallery-viewer--image.js
 * @summary Cypress spec for End-to-End UI testing.
 * @requires DTRT WordPress Plugin Boilerplate Generator 0.8.13
 * {@link https://github.com/dotherightthing/wpdtrt-plugin-boilerplate/wiki/Testing-&-Debugging:-Cypress.io|Testing & Debugging: Cypress.io}
 * @todo Test user selected default image (if not first in set)
 */

/* eslint-disable prefer-arrow-callback */
/* eslint-disable max-len */

// Test principles:
// ARRANGE: SET UP APP STATE > ACT: INTERACT WITH IT > ASSERT: MAKE ASSERTIONS

// Passing arrow functions (“lambdas”) to Mocha is discouraged
// https://mochajs.org/#arrow-functions
/* eslint-disable func-names */

const componentClass = 'wpdtrt-gallery';
const sectionId = 'section-a-new-bike';
const galleryId = 'galleryid-12';

describe('DTRT Gallery - Image Viewer', function () {
    before(function () {
        // load local tour diary page
        cy.visit('/tourdiaries/asia/east-asia/china-1/1/newzealand-to-china/');
    });

    beforeEach(function () {
        // @aliases
        // Aliases are cleared between tests
        // https://stackoverflow.com/questions/49431483/using-aliases-in-cypress

        // a gallery viewer that is below the fold won't have been transformed yet
        // the default gallery item is an image, which is accessible
        cy.get(`#${ sectionId } .${ componentClass }`)
            .as('wpdtrtGallery');

        cy.get(`#${galleryId}-tabpanel-1`)
            .as('wpdtrtGalleryTabPanel');

        // the thumbnail gallery that populates the viewer
        // NOT WORKING - requires scroll-to fix, test after gallery is tested
        // cy.get('@wpdtrtGallery').siblings('.gallery')
        //  .as('galleryBelowFold');

        // @aliases for injected elements
        // cy.get(`#${ sectionId } .child-injected`).as('componentInjected');
    });

    describe('A. Configuration', function () {
        it('Plugin UI object exists', function () {
            cy.resetUI();

            // check that the plugin object is available
            cy.window()
                .should('have.property', 'wpdtrtGalleryUi');

            // check that it's an object
            cy.window().then((win) => {
                expect(win.wpdtrtGalleryUi).to.be.a('object');
            });
        });
    });

    // default viewer attributes in
    // template-parts/content-heading.php
    // when element is offscreen
    describe('B. Default state', function () {
        it('2. Is enabled', function () {
            cy.get('@wpdtrtGallery')
                .should('have.attr', 'data-enabled', 'true');
        });

        it('3. Does contain an image', function () {
            cy.get('@wpdtrtGallery').find('.wpdtrt-gallery-viewer__img-wrapper > img')
                .should('exist');
        });

        it('4. Does not contain a panorama', function () {
            // check that the default markup has been transformed
            cy.get('@wpdtrtGalleryTabPanel')
                .should('not.have.attr', 'data-panorama');
        });

        it('6. Does contain an expand button', function () {
            cy.get('@wpdtrtGalleryTabPanel').find('.wpdtrt-gallery-viewer__expand')
                .should('exist');
        });

        it('7. Is not expanded', function () {
            cy.get('@wpdtrtGallery')
                .should('have.attr', 'data-expanded', 'false');
        });

        it('8. Passes Tenon validation', function () {
            this.skip();

            cy.get('@wpdtrtGallery').then((galleryViewer) => {
                // Add a wrapper around the component so that the HTML can be submitted independently
                cy.task('tenonAnalyzeHtml', `${galleryViewer.wrap('<div/>').parent().html()}`)
                    .its('results').should('eq', []);
            });
        });
    });

    describe('C. Enhanced state (system selection)', function () {
        it('1. Above the fold', function () {
            cy.resetUI();

            // scroll component into view,
            // as Cypress can't always 'see' elements below the fold
            cy.get('@wpdtrtGallery').find('.wpdtrt-gallery-viewer')
                .scrollIntoView({
                    duration: 1500,
                    offset: {
                        top: 0,
                        left: 0
                    }
                })
                .should('be.visible');
        });

        it('2. Is enabled', function () {
            cy.get('@wpdtrtGallery')
                .should('have.attr', 'data-enabled', 'true');
        });

        it('3. Does contain a landscape image, described accessibly', function () {
            cy.get('@wpdtrtGalleryTabPanel').find('.wpdtrt-gallery-viewer__img-wrapper > img')
                .should('exist')
                .should('have.css', 'width', '688.453125px')
                .should('have.css', 'height', '292.890625px')
                .invoke('attr', 'alt')
                .should('match', /.+/); // alt !== ''
        });

        it('4. Does not contain a panorama', function () {
            // check that the default markup has been transformed
            cy.get('@wpdtrtGalleryTabPanel')
                .should('not.have.attr', 'data-panorama');
        });

        it('6. Does contain a visible expand button, shown accessibly', function () {
            cy.get('@wpdtrtGalleryTabPanel').find('.wpdtrt-gallery-viewer__expand')
                .should('exist')
                .should('be.visible')
                .should('have.attr', 'aria-controls', `${galleryId }-tabpanel-1-media`)
                .should('contain.text', 'Expand');
        });

        it('7. Is not expanded', function () {
            cy.get('@wpdtrtGallery')
                .should('have.attr', 'data-expanded', 'false')
                .should('have.attr', 'data-expanded-locked', 'false');
        });

        it('8. Passes Tenon validation', function () {
            this.skip();

            cy.get('@wpdtrtGallery').then((galleryViewer) => {
                // Add a wrapper around the component so that the HTML can be submitted independently
                cy.task('tenonAnalyzeHtml', `${galleryViewer.wrap('<div/>').parent().html()}`)
                    .its('results').should('eq', []);
            });
        });
    });

    describe('D. Expanded state (system selection)', function () {
        it('1. Is expanded, expanded accessibly', function () {
            cy.get('@wpdtrtGalleryTabPanel').find('.wpdtrt-gallery-viewer__expand')
                .click()
                .should('contain.text', 'Collapse');

            cy.get('@wpdtrtGallery')
                .should('have.attr', 'data-expanded', 'true')
                .should('have.attr', 'data-expanded-locked', 'false');
        });

        it('2. Does contain a taller landscape image, described accessibly', function () {
            // can we use this.attr.src here or invoke?

            cy.get('@wpdtrtGalleryTabPanel').find('.wpdtrt-gallery-viewer__img-wrapper > img')
                .should('exist')
                .should('have.css', 'width', '688.453125px')
                .should('have.css', 'height', '387.59375px')
                .invoke('attr', 'alt')
                .should('match', /.+/); // alt !== ''
        });

        it('4. Passes Tenon validation', function () {
            this.skip();

            cy.get('@wpdtrtGallery').then((galleryViewer) => {
                // Add a wrapper around the component so that the HTML can be submitted independently
                cy.task('tenonAnalyzeHtml', `${galleryViewer.wrap('<div/>').parent().html()}`)
                    .its('results').should('eq', []);
            });
        });
    });
});
