describe('Test datetime component', () => {

    beforeEach(() => {
        const valuesFile = './cypress/yaml/datetime/values.yaml';
        cy.exec(`rm -f ${valuesFile}`);

        cy.visit('/datetime');
    });

    it('datetime attributes', () => {
        cy.get('datetime-component')
            .find('input')
            .should('have.attr', 'name', 'datetime_allattributes')
            .and('have.attr', 'type', 'datetime-local')
            .and('have.attr', 'placeholder', 'placeholder value for input')
            .and('have.prop', 'value', '');

        cy.get('datetime-component')
            .find('input')
            .should('have.attr', 'readonly');
    });

    it('datetime required validation', () => {
        cy.get('datetime-component')
            .first()
            .find('label')
            .should('not.contain.text', '*')
            .and('contain.text', 'datetime label');
    });

    it('datetime start and end validation', () => {
        // valid datetime within range
        cy.get('datetime-component')
            .find('input[name="datetime_writeable"]')
            .clear()
            .type('2025-06-15T12:30');

        cy.get('button[name="save"]').click();
        cy.get('.notification.is-danger').should("not.exist");

        // edge case: just before start (should fail)
        cy.get('datetime-component')
            .find('input[name="datetime_writeable"]')
            .clear()
            .type('2025-01-01T11:59');

        cy.get('button[name="save"]').click();
        cy.get('.notification.is-danger').should("be.visible");
        cy.get("p.is-danger").should("be.visible").and("include.text", "2025-01-01 12:00");

        // edge case: exactly on end (should pass)
        cy.get('datetime-component')
            .find('input[name="datetime_writeable"]')
            .clear()
            .type('2025-12-31T12:00');

        cy.get('button[name="save"]').click();
        cy.get('.notification.is-danger').should("not.exist");

        // edge case: just after end (should fail)
        cy.get('datetime-component')
            .find('input[name="datetime_writeable"]')
            .clear()
            .type('2025-12-31T12:01');

        cy.get('button[name="save"]').click();
        cy.get('.notification.is-danger').should("be.visible");
        cy.get("p.is-danger").should("be.visible").and("include.text", "2025-12-31 12:00");
    });

});
