/**
 * @file DTRT Gallery Viewer wpdtrt-gallery-viewer.js
 * @summary
 *     Cypress spec for UI testing
 * @version   0.0.1
 * @since     0.8.4 DTRT WordPress Plugin Boilerplate Generator
 */

/* eslint-disable prefer-arrow-callback */
/* eslint-disable max-len */
/* global cy */

// Test principles:
// ARRANGE: SET UP APP STATE > ACT: INTERACT WITH IT > ASSERT: MAKE ASSERTIONS

// Aliases are cleared between tests
// https://stackoverflow.com/questions/49431483/using-aliases-in-cypress

// Passing arrow functions (“lambdas”) to Mocha is discouraged
// https://mochajs.org/#arrow-functions

const componentId = "signposts";

describe("Gallery Viewer", function () {

  before(function() {
    // load local web page
    cy.visit("/tourdiaries/asia/east-asia/russia/18/yelantsy-to-olkhon-island/");
  });

  beforeEach(function () {
    // refresh the page to reset the UI state
    cy.reload();

    // @aliases
    cy.get(`#${componentId}`).as("gallerySection");
    cy.get(`#${componentId} .gallery-viewer`).as("galleryViewer");

    // default viewer attributes
    cy.get("@galleryViewer")
      .should("have.attr", "id", "[]-viewer")
      .should("have.attr", "data-has-gallery", "false")
      .should("have.attr", "data-expanded", "false")
      .should("not.have.attr", "data-expanded-user");

    // scroll gallery viewer into view, so that its images lazy load
    // helps Cypress to "see" elements below the fold
    cy.get("@gallerySection")
      .scrollIntoView({
        offset: {
          top: 100,
          left: 0
        }
      })
      .should("be.visible");

    // @aliases for injected elements
    cy.get(`#${componentId} .gallery-viewer--expand`).as("galleryViewerExpand");
  });

  describe("Setup", function () {
    it("Has prerequisites", function () {
      // check that the plugin object is available
      cy.window().should("have.property", "wpdtrt_gallery_ui")

      // check that it's an object
      cy.window().then((win) => {
        expect(win.wpdtrt_gallery_ui).to.be.a("object");
      });
    });
  });

  describe("Lazy load", function() {
    it("Loads", function() {
      // check that the gallery viewer has been assigned the correct ID
      cy.get("@galleryViewer")
        .should("have.attr", "id", `${componentId}-viewer`)
        .should("have.attr", "data-has-gallery", "true")
        .should("have.attr", "data-expanded", "false")
        .should("not.have.attr", "data-expanded-user");

      // check that the Expand button has the correct attributes and text
      cy.get("@galleryViewerExpand")
        .should("have.attr", "aria-controls", `${componentId}-viewer`)
        .should("have.attr", "aria-expanded", "false")
        .contains("Show full image");

      // test the accessibility of the viewer using Tenon.io
      // TODO: add a wrapper around the Viewer so that the viewer snippet can be submitted independently
      // See also https://github.com/dotherightthing/wpdtrt-gallery/blob/master/tests/test-wpdtrt-gallery.php - tenon()
      cy.get("@gallerySection").then((gallerySection) => {
        // testing the contents rather than the length gives a more useful error object
        cy.task("tenonAnalyzeHtml", `${gallerySection.html()}`)
          // an empty resultSet indicates that there are no errors
          .its("resultSet").should("be.empty");
      });
    });
  });

  describe("Expand/Collapse", function () {
    it("Expands", () => {
      // check that the Expand button has the correct attributes and text
      cy.get("@galleryViewerExpand")
        .should("have.attr", "aria-controls", `${componentId}-viewer`)
        .should("have.attr", "aria-expanded", "false")
        .contains("Show full image");

      // click the Expand button
      cy.get("@galleryViewerExpand")
        .click();

      // check that the viewer remembers the action
      cy.get("@galleryViewer")
        .should("have.attr", "data-expanded", "true")
        .should("have.attr", "data-expanded-user", "true");

      // check that the Expand button has the correct attributes and text
      cy.get("@galleryViewerExpand")
        .should("have.attr", "aria-controls", `${componentId}-viewer`)
        .should("have.attr", "aria-expanded", "true")
        .contains("Show cropped image");
     });

    it("Collapses", function() {
      // click the Expand button
      cy.get("@galleryViewerExpand")
        .click();

      // check that the Expand button has the correct attributes and text
      cy.get("@galleryViewerExpand")
        .should("have.attr", "aria-controls", `${componentId}-viewer`)
        .should("have.attr", "aria-expanded", "true")
        .contains("Show cropped image");

      // click the Expand button
      cy.get("@galleryViewerExpand")
        .click();

      // check that the viewer remembers the action
      cy.get("@galleryViewer")
        .should("have.attr", "data-expanded", "false")
        .should("have.attr", "data-expanded-user", "false");

      // wait for button attributes to update
      cy.wait(500);

      // check that the Expand button has the correct attributes and text
      cy.get("@galleryViewerExpand")
        .should("have.attr", "aria-controls", `${componentId}-viewer`)
        .should("have.attr", "aria-expanded", "false")
        .contains("Show full image");
     });
  });
});
