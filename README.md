# Elegant ORM [BETA]

ORM for CodeIgniter based on Laravel's Eloquent. The Elegant ORM brings the beauty and simplicity of working with Eloquent ORM in Laravel to CodeIgniter framework.

## Installation

- Download the zipball
- Extract to your `application/libraries` directory
- In your `config/autoload.php` file, add `elegant-orm/elegant` to `$autoload['libraries']`. So it will look like this:

  ```php
  $autoload['libraries'] = array('elegant-orm/elegant');
  ```

## Usage
### Defining Models
Models in Elegant ORM (as in other ORMs) represent a single table to work with. To define a model, it's about the same with non-ORM CodeIgniter, but instead of extending `CI_Model`, the ORM model should extends `Elegant\Model` class.

*Example:* Model for table user, located in `models/user.php`

```php
class User extends Elegant\Model {
  protected $table = "user";
}
```

The `$table` property is used to tell which table the model will work with. There are also several properties to customize the model configuration.

### Model properties
Here are some properties you can use to customize the model

- `$table` : to define the table name. This property is mandatory to set
- `$db_group` : to define which database group the model will connect. The groups can be found in `config/database.php`. By default it uses "default" group
- `$primary` : to define the column name of the table's primary key. Default is "id". If your PK has other name than "id" you should change this
- `$incrementing` : to define whether your PK is auto-increment. Default value is `true`. If you'd like to generate your Primary Key value by custom function, set this to `false`

## For now you can use some of Laravel Eloquent method and functionality. Read the documentation here http://laravel.com/docs/eloquent and try to apply those in your CodeIgniter application
