describe('Test date component', () => {

    beforeEach(() => {
        const valuesFile = './cypress/yaml/date/values.yaml';
        cy.exec(`rm -f ${valuesFile}`);

        cy.visit('/date');
    });

    it('date attributes', () => {
        cy.get('date-component')
            .find('input')
            .should('have.attr', 'name', 'date_allattributes')
            .and('have.attr', 'type', 'date')
            .and('have.attr', 'placeholder', 'placeholder value for input')
            .and('have.prop', 'value', '');

        cy.get('date-component')
            .find('input')
            .should('have.attr', 'readonly');
    });

    it('date required validation', () => {
        cy.get('date-component')
            .first()
            .find('label')
            .should('not.contain.text', '*')
            .and('contain.text', 'date label');
    });

    it('date start and end validation', () => {
        // valid date within range
        cy.get('date-component')
            .find('input[name="date_writeable"]')
            .clear()
            .type('2024-06-15');

        cy.get('button[name="save"]').click();
        cy.get('.notification.is-danger').should("not.exist");

        // before start (should fail)
        cy.get('date-component')
            .find('input[name="date_writeable"]')
            .clear()
            .type('2022-12-31');

        cy.get('button[name="save"]').click();
        cy.get('.notification.is-danger').should("be.visible");
        cy.get("p.is-danger").should("be.visible").and("include.text", "2023-01-01");

        // after end (should fail)
        cy.get('date-component')
            .find('input[name="date_writeable"]')
            .clear()
            .type('2026-01-01');

        cy.get('button[name="save"]').click();
        cy.get('.notification.is-danger').should("be.visible");
        cy.get("p.is-danger").should("be.visible").and("include.text", "2025-12-31");
    });

});
