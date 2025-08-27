describe('Test checklist component', () => {

    beforeEach(() => {
        cy.visit('/checklist');
    });

    it('checklist attributes', () => {
        cy.get('checklist-component')
            .find('input[type="checkbox"]')
            .should('have.length', 3)
            .each(($checkbox) => {
                cy.wrap($checkbox)
                    .should('have.attr', 'name', 'checklist_allattributes')
                    .and('have.attr', 'type', 'checkbox');
            });
    });

    it('checklist validation', () => {
        cy.get('checklist-component')
            .find('label.label')
            .should('not.contain.text', '*')
            .and('contain.text', 'checklist label');
    });

    it('checklist options', () => {
        cy.get('checklist-component')
            .find('input[type="checkbox"]')
            .eq(0)
            .should('have.attr', 'value', 'First choice')
            .and('be.checked');
        
        cy.get('checklist-component')
            .find('input[type="checkbox"]')
            .eq(1)
            .should('have.attr', 'value', 'Second choice')
            .and('not.be.checked');
        
        cy.get('checklist-component')
            .find('input[type="checkbox"]')
            .eq(2)
            .should('have.attr', 'value', 'Third choice')
            .and('not.be.checked');
    });

});
