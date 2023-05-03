# races

Setup:

1. create a database
2. create your .env.local and set database details
3. create your .env.test.local and set test database details
4. run composer install
5. run doctrine migrations with: php bin/console --env=test  doctrine:migrations:migrate 
6. run doctrine migrations on test database with: php bin/console --env=test  doctrine:migrations:migrate
7. Tests are configured with dama/doctrine-test-bundle to erase data after they are done. You can run test with: php bin/phpunit
