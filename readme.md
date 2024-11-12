# Simple MVC Database and querybuilder

### Installation:
pull repo into /app/Database/ ti include. Make sure to update or add the database and connection classes in the /app/Application.php class

### How to use:
```php
    $connection = new Connection('sqlite');
    $builder = new Builder($connection, 'sqlite');
    $builder->build($builder->builder()
        ->createTable('users')
        ->primary()
        ->string("email", 255)->notNull()->unique()
        ->string("first_name", 255)->notNull()
        ->string("last_name", 255)->notNull()
        ->string("password", 255)->notNull()
        ->addTimeStamps()
    );
    
    $builder->build($builder->builder()
        ->addColumn('users')
        ->boolean('active')
        ->notNull()
        ->defaults(1)
    );
    
    $builder->build($builder->builder()
        ->insert('users', [
            'email'=>'test@example.com',
            'first_name'=>'Jesse',
            'last_name'=>'Fender',
            'password'=>'secret'
        ])
    );

    $found = $builder->build($builder->builder()
        ->select('users', ['*'])
        ->between('id', 1, 5)
        ->andWhere('active', '=', 1)
    );
```
Options for dialects are mysql and sqlite
for inserts the values are [\$key => \$value] pairs, with \$key being the column name, and \$value is the value to insert.
for conditionals if you have one condition use the or[conditionalName] or and[conditionalName] to use and/or or on where clauses.
joins are special case and you should read the structure for join in /Database/queryBuilder/Dialect/Dialect line 189.

this is still in testing and may have changes as development gets my attention.