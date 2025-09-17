describe('Test textarea component', () => {

    beforeEach(() => {
        cy.visit('/textarea');
    });

    afterEach(() => {
        const valuesFile = './cypress/yaml/textarea/values.yaml';
        cy.exec(`rm -f ${valuesFile}`);
    });

    it('textarea attributes', () => {
        cy.get('textarea-component')
            .find('textarea')
            .should('have.attr', 'name', 'textarea_allattributes')
            .and('have.attr', 'placeholder', 'placeholder value for input')
            .and('have.prop', 'value', '')
            .and('have.attr', 'rows', '7')
            .and('have.attr', 'cols', '40');

        cy.get('textarea-component')
            .find('textarea')
            .should('not.have.attr', 'readonly');
    });

    it('textarea validation', () => {
        cy.get('textarea-component')
            .find('label')
            .should('not.contain.text', '*')
            .and('contain.text', 'textarea label');
    });

    it('maxlength validation', () => {
        // Test that textarea accepts text within maxlength limit
        cy.get('textarea-component')
            .find('textarea')
            .clear()
            .type('This is a short text within the limit of one hundred characters for the textarea component.');

        cy.get('button[name="save"]').click();
        cy.get('.notification.is-danger').should("not.exist");

        // validation fails when exceeding maxlength of 100 chars and error messages are displayed
        const longText = 'This is a very long text that definitely exceeds the maximum length limit of one hundred characters set for this textarea component and should trigger a validation error when submitted.';
        cy.get('textarea-component')
            .find('textarea')
            .clear()
            .type(longText);

        cy.get('button[name="save"]').click();
        cy.get('.notification.is-danger').should("be.visible");
        cy.get("p.is-danger").should("be.visible").and("include.text", "100");
    });

});
