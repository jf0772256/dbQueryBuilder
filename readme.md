# Simple MVC Database and querybuilder

### Installation:
Pull repo into /app/Database/ to include. Make sure to update or add the database and connection classes in the /app/Application.php class
Installation can also be done by downloading the remo and copy pasting the Database and queryBuilder directories into the /app/Database/ directory.
This is a required repo of the Simple MVC project. It is not required that you use the Builder class but the reops /Database/ files are critical to the framework functioning.


### How to use:
```php
    use Jesse\SimplifiedMVC\Database\Database\Connection;
    use Jesse\SimplifiedMVC\Database\queryBuilder\Builder;    

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
Options for dialects are currently mysql, sqlite, and sqlserver.


For inserts the values are [\$key => \$value] pairs, with \$key being the column name, and \$value is the value to insert.

For conditionals if you have one condition use the or[conditionalName] or and[conditionalName] to use and/or or on where clauses.

Joins are special case and you should read the structure for join in /Database/queryBuilder/Dialect/Dialect line 189.

This is still in testing and may have changes as development gets my attention.