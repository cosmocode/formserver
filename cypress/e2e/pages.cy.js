describe('Test pages component', () => {

    beforeEach(() => {
        cy.visit('/pages');
    });

    afterEach(() => {
        const valuesFile = './cypress/yaml/pages/values.yaml';
        cy.exec(`rm -f ${valuesFile}`);
    });

    it('pages component structure', () => {
        // Check that the pages component exists
        cy.get('pages-component').should('exist');

        // Check that tabs are present
        cy.get('.tabs ul li').should('have.length', 3);

        // Check tab labels
        cy.get('.tabs ul li').eq(0).should('contain.text', 'Personal Info');
        cy.get('.tabs ul li').eq(1).should('contain.text', 'Contact Details');
        cy.get('.tabs ul li').eq(2).should('contain.text', 'Preferences');

        // Check that first tab is active
        cy.get('.tabs ul li').eq(0).should('have.class', 'is-active');

        // Check that first page is visible
        cy.get('.pages .page').eq(0).should('be.visible');
        cy.get('.pages .page').eq(1).should('not.be.visible');
        cy.get('.pages .page').eq(2).should('not.be.visible');
    });

    it('tab navigation', () => {
        // Click on second tab
        cy.get('.tabs ul li').eq(1).find('a').click();

        // Check that second tab is now active
        cy.get('.tabs ul li').eq(1).should('have.class', 'is-active');
        cy.get('.tabs ul li').eq(0).should('not.have.class', 'is-active');

        // Check that second page is visible
        cy.get('.pages .page').eq(1).should('be.visible');
        cy.get('.pages .page').eq(0).should('not.be.visible');

        // Click on third tab
        cy.get('.tabs ul li').eq(2).find('a').click();

        // Check that third tab is now active
        cy.get('.tabs ul li').eq(2).should('have.class', 'is-active');
        cy.get('.tabs ul li').eq(1).should('not.have.class', 'is-active');

        // Check that third page is visible
        cy.get('.pages .page').eq(2).should('be.visible');
        cy.get('.pages .page').eq(1).should('not.be.visible');
    });

    it('validation error highlighting on tabs', () => {
        // Fill in some fields but leave required ones empty to trigger validation errors

        // Page 1: Leave first_name empty (required)
        cy.get('input[name="pages_component.page1.last_name"]').type('Doe');

        // Page 2: Leave email empty (required)
        cy.get('.tabs ul li').eq(1).find('a').click();
        cy.get('input[name="pages_component.page2.phone"]').type('123-456-7890');

        // Page 3: Leave contact_method empty (required)
        cy.get('.tabs ul li').eq(2).find('a').click();
        cy.get('textarea[name="pages_component.page3.comments"]').type('Some comments');

        // Try to submit the form
        cy.get('button[name="send"]').click();

        // Check that validation errors are shown
        cy.get('.notification.is-danger').should('be.visible');

        // Check that tabs with validation errors have is-danger class
        cy.get('.tabs ul li').eq(0).should('have.class', 'has-errors'); // Page 1 has missing first_name
        cy.get('.tabs ul li').eq(1).should('have.class', 'has-errors'); // Page 2 has missing email and address
        cy.get('.tabs ul li').eq(2).should('have.class', 'has-errors'); // Page 3 has missing contact_method
    });

    it('successful form submission', () => {
        // Fill in all required fields

        // Page 1
        cy.get('input[name="pages_component.page1.first_name"]').type('John');
        cy.get('input[name="pages_component.page1.last_name"]').type('Doe');
        cy.get('input[name="pages_component.page1.birth_date"]').type('1990-05-15');
        cy.get('input[name="pages_component.page1.age"]').type('33');

        // Page 2
        cy.get('.tabs ul li').eq(1).find('a').click();
        cy.get('input[name="pages_component.page2.email"]').type('john.doe@example.com');
        cy.get('textarea[name="pages_component.page2.address"]').type('123 Main St, Anytown, USA');

        // Page 3
        cy.get('.tabs ul li').eq(2).find('a').click();
        cy.get('input[name="pages_component.page3.contact_method"][value="Email"]').click();

        // Submit the form
        cy.get('button[name="send"]').click();

        // Check that no validation errors are shown
        cy.get('.notification.is-danger').should('not.exist');

        // Check that no tabs have is-danger class
        cy.get('.tabs ul li.is-danger').should('not.exist');
    });

});
