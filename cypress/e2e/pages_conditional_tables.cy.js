describe('Conditional Choices in Tables within Pages', () => {
    beforeEach(() => {
        cy.visit('/pages_conditional_tables');
    });

    afterEach(() => {
        cy.exec('rm -f ./cypress/yaml/pages_conditional_tables/values.yaml');
        cy.clearOPFS();
    });

    const prefix = 'pages_component.page1.products_table';

    it('should have correct nested field names', () => {
        cy.get(`select[name="${prefix}.COL1.category"]`).should('exist');
        cy.get(`select[name="${prefix}.COL1.product"]`).should('exist');
        cy.get(`input[name="${prefix}.COL1.quantity"]`).should('exist');

        cy.get(`select[name="${prefix}.COL2.category"]`).should('exist');
        cy.get(`select[name="${prefix}.COL2.product"]`).should('exist');
        cy.get(`input[name="${prefix}.COL2.quantity"]`).should('exist');

        cy.get(`select[name="${prefix}.COL3.category"]`).should('exist');
        cy.get(`select[name="${prefix}.COL3.product"]`).should('exist');
        cy.get(`input[name="${prefix}.COL3.quantity"]`).should('exist');
    });

    it('should show fruit choices when category is fruits in column 1', () => {
        cy.get(`select[name="${prefix}.COL1.category"]`).select('some fruits');

        cy.get(`select[name="${prefix}.COL1.product"] option`).then($options => {
            const values = [...$options].map(o => o.value).filter(v => v !== '');
            expect(values).to.deep.equal(['apple', 'banana', 'orange']);
        });
    });

    it('should show vegetable choices when category is vegetables in column 1', () => {
        cy.get(`select[name="${prefix}.COL1.category"]`).select('some vegetables');
        cy.wait(500);

        cy.get(`select[name="${prefix}.COL1.product"] option`).then($options => {
            const values = [...$options].map(o => o.value).filter(v => v !== '');
            expect(values).to.deep.equal(['carrot', 'broccoli', 'lettuce']);
        });
    });

    it('should maintain independent choices across different table columns', () => {
        cy.get(`select[name="${prefix}.COL1.category"]`).select('some fruits');
        cy.get(`select[name="${prefix}.COL1.product"] option`).then($options => {
            const values = [...$options].map(o => o.value).filter(v => v !== '');
            expect(values).to.deep.equal(['apple', 'banana', 'orange']);
        });

        cy.get(`select[name="${prefix}.COL2.category"]`).select('some vegetables');
        cy.get(`select[name="${prefix}.COL2.product"] option`).then($options => {
            const values = [...$options].map(o => o.value).filter(v => v !== '');
            expect(values).to.deep.equal(['carrot', 'broccoli', 'lettuce']);
        });

        // Column 1 should still show fruits
        cy.get(`select[name="${prefix}.COL1.product"] option`).then($options => {
            const values = [...$options].map(o => o.value).filter(v => v !== '');
            expect(values).to.deep.equal(['apple', 'banana', 'orange']);
        });
    });

    it('should update product choices when category is changed', () => {
        cy.get(`select[name="${prefix}.COL1.category"]`).select('some fruits');
        cy.get(`select[name="${prefix}.COL1.product"]`).select('apple');

        cy.get(`select[name="${prefix}.COL1.category"]`).select('some vegetables');
        cy.wait(500);

        cy.get(`select[name="${prefix}.COL1.product"] option`).then($options => {
            const values = [...$options].map(o => o.value).filter(v => v !== '');
            expect(values).to.deep.equal(['carrot', 'broccoli', 'lettuce']);
        });

        cy.get(`select[name="${prefix}.COL1.product"]`).should('have.value', '');
    });

    it('should test all three columns independently', () => {
        cy.get(`select[name="${prefix}.COL1.category"]`).select('some vegetables');
        cy.wait(500);
        cy.get(`select[name="${prefix}.COL1.product"] option`).then($options => {
            const values = [...$options].map(o => o.value).filter(v => v !== '');
            expect(values).to.deep.equal(['carrot', 'broccoli', 'lettuce']);
        });

        cy.get(`select[name="${prefix}.COL2.category"]`).select('some vegetables');
        cy.wait(500);
        cy.get(`select[name="${prefix}.COL2.product"] option`).then($options => {
            const values = [...$options].map(o => o.value).filter(v => v !== '');
            expect(values).to.deep.equal(['carrot', 'broccoli', 'lettuce']);
        });

        cy.get(`select[name="${prefix}.COL3.category"]`).select('some vegetables');
        cy.wait(500);
        cy.get(`select[name="${prefix}.COL3.product"] option`).then($options => {
            const values = [...$options].map(o => o.value).filter(v => v !== '');
            expect(values).to.deep.equal(['carrot', 'broccoli', 'lettuce']);
        });
    });

    it('should work with pre-filled values on initial render', () => {
        cy.get(`select[name="${prefix}.COL1.category"]`).select('some fruits');
        cy.get(`select[name="${prefix}.COL1.product"]`).select('banana');
        cy.get(`input[name="${prefix}.COL1.quantity"]`).type('5');

        cy.get('button[name="save"]').click();
        cy.get('.notification > p').should('be.visible');

        cy.visit('/pages_conditional_tables');

        cy.get(`select[name="${prefix}.COL1.category"]`).should('have.value', 'some fruits');
        cy.get(`select[name="${prefix}.COL1.product"]`).should('have.value', 'banana');
        cy.get(`select[name="${prefix}.COL1.product"] option`).then($options => {
            const values = [...$options].map(o => o.value).filter(v => v !== '');
            expect(values).to.deep.equal(['apple', 'banana', 'orange']);
        });
    });
});
