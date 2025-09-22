describe('Test textinput component', () => {

    beforeEach(() => {
        cy.visit('/textinput');
    });

    afterEach(() => {
        const valuesFile = './cypress/yaml/textinput/values.yaml';
        cy.exec(`rm -f ${valuesFile}`);
    });

    it('textinput attributes', () => {
        cy.get('textinput-component')
            .first()
            .find('input')
            .should('have.attr', 'name', 'txt_allattributes')
            .and('have.attr', 'type', 'text')
            .and('have.attr', 'placeholder', 'placeholder value for input')
            .and('have.prop', 'value', '');

        cy.get('textinput-component')
            .first()
            .find('input')
            .should('have.attr', 'readonly');

        // test suffix
        cy.get('textinput-component')
            .first()
            .find(".field")
            .should("have.class", "has-addons")
            .find("> p.control")
            .should('have.length', 2)
            .last()
            .find("> a.button.is-static")
            .should("be.visible");
    });

    it('textinput required hint', () => {
        cy.get('textinput-component')
            .first()
            .find('label')
            .should('not.contain.text', '*')
            .and('contain.text', 'readonly text');
    });

    it('maxlength validation', () => {
        cy.get('textinput-component')
            .find('input[name="txt_writeable"]')
            .clear()
            .type('This is a short text within limit');

        cy.get('button[name="save"]').click();
        cy.get('.notification.is-danger').should("not.exist");

        // validation fails when exceeding maxlength (50 chars) and error messages are displayed
        const longText = 'This is a very long text that exceeds the maximum length limit of fifty characters';
        cy.get('textinput-component')
            .find('input[name="txt_writeable"]')
            .clear()
            .type(longText);

        cy.get('button[name="save"]').click();
        cy.get('.notification.is-danger').should("be.visible");
        cy.get("p.is-danger").should("be.visible").and("include.text", "50");
    });
});
