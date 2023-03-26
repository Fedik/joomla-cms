describe('Test that the list view ', () => {
  it('can display a list of contacts in menu item', () => {
    cy.db_createContact({ name: 'automated test contact 1', featured: 1 })
      .then(() => cy.db_createContact({ name: 'automated test contact 2', featured: 1 }))
      .then(() => cy.db_createContact({ name: 'automated test contact 3', featured: 1 }))
      .then(() => cy.db_createContact({ name: 'automated test contact 4', featured: 1 }))
      .then(() => cy.db_createMenuItem({ title: 'automated test', link: 'index.php?option=com_contact&view=featured', path: '?option=com_contact&view=featured' }))
      .then(() => {
        cy.visit('/');
        cy.get('a:contains(automated test)').click();

        cy.contains('automated test contact 1');
        cy.contains('automated test contact 2');
        cy.contains('automated test contact 3');
        cy.contains('automated test contact 4');
      });
  });

  it('can display a list of contacts without menu item', () => {
    cy.db_createContact({ name: 'automated test contact 1' })
      .then(() => cy.db_createContact({ name: 'automated test contact 2' }))
      .then(() => cy.db_createContact({ name: 'automated test contact 3' }))
      .then(() => cy.db_createContact({ name: 'automated test contact 4' }))
      .then(() => {
        cy.visit('/index.php?option=com_contact&view=category&id=4');

        cy.contains('automated test contact 1');
        cy.contains('automated test contact 2');
        cy.contains('automated test contact 3');
        cy.contains('automated test contact 4');
      });
  });

  it('can open the contact form in the default layout', () => {
    cy.db_createContact({ name: 'contact 1' })
      .then(() => cy.db_createMenuItem({ title: 'automated test', link: 'index.php?option=com_contact&view=category&id=4', path: '?option=com_contact&view=category&id=4' }))
      .then(() => {
        cy.doFrontendLogin(Cypress.env('username'), Cypress.env('password'));
        cy.visit('/');
        cy.get('a:contains(automated test)').click();
        cy.get('a:contains(New Contact)').click();

        cy.get('#adminForm').should('exist');
      });
  });
});
