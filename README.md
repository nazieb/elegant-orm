## Project Abandoned

Hello, thank you for your interest on Elegant ORM for Code Igniter. But I'm really sorry to say that I can't continue to maintain this library. As you may notice there are some issues & pull requests that still waiting to be handled but I just can't find any spare time to check them.

If you're still interested in using this library, there are some good folks who forked this project and continue to maintain them, check the Network page (https://github.com/nazieb/elegant-orm/network) to see those forked projects.

Once again, thank you and good luck!

# Elegant ORM [BETA]

ORM for CodeIgniter based on Laravel's Eloquent. The Elegant ORM brings the beauty and simplicity of working with Eloquent ORM in Laravel to CodeIgniter framework.

## Installation

- Download the zipball of the latest release at https://github.com/nazieb/elegant-orm/releases
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

## Querying
### Retrieve all models
```php
$users = User::all();
foreach($users as $user)
{
  echo $user->name;
}
```

### Find a model by primary key
```php
$user = User::find(1);
echo $user->name;
```
You can also pass multiple IDs or an array to get multiple records.
```php
$users = User::find(1, 2, 3);
// or
$users = User::find( array(1, 2, 3) );
```

### Custom Query
You can still use Code Igniter Active Record to generate a custom query.
```php
$users = User::where('status', 1)->get();

foreach($users as $user) echo $user->name;
```
Or if you only want to retrieve the first record, you can use `first()` method
```php
$user = User::where('status', 1)->first();
echo $user->name;
```

### 'Pluck'-ing
Plucking is the way to retrieve a single column value of the first record.
```php
// Returns the `name` column of the first row
echo 'Newest user is: ' . User::order_by('id', 'desc')->pluck('name');
```

### Selecting Specific Columns
By default Elegant will generate a `SELECT *` query for all examples above. If you think this is a bad practice, you can select only specific columns you need in several ways:

```php
User::all( array('id', 'username') );
User::where('status', 1)->get( array('id', 'username') );
User::select( array('id', 'username') )->get();
User::where('status', 1)->first( array('id', 'username') );
```

Note: for now `find()` method doesn't support selecting specific column.

### Aggregates Methods
Elegant also provides aggregates method, such as `max`, `min`, `avg`, `sum`, and `count`. You can call these methods right away or chain them with some CI Active Records method.

```php
$total = User::count();

$max = Product::max('price');

// Example of chaining with where()
$min = Product::where('category', 1)->min('price');

$avg = Product::avg('price');

// Another chaining example
$sum = Order::where('status', 1)->sum('price');
```

## Create, Update & Delete
### Creating A New Model
```php
$user =  new User;

$user->name = 'John Doe';
$user->email = 'dummy@example.com';

$user->save();
```

After saving the record, if your model uses auto increment primary key, the generated insert id will be set to the object. So if you use example above, you can show the new user's ID right away
```php
echo "New User ID: " . $user->id;
```
Note that the property isn't always `id`. It depends on the `primary` property you've set before.

Alternatively, you can use `create` method to create new models.
```php
$user = User::create( array('name' => 'John Doe', 'email' => 'dummy@example.com') );

// create() method will return a newly created model or false if inserting fails
echo $user->id;
```

### Updating Models
#### Updating Retrieved Model
```php
$user = User::find(1);

$user->name = 'Jack Doe';
$user->save();
```

#### Mass Updating
You still can use CodeIgniter Active Record to generate a custom query before updating.
```php
User::where('status', 1)->update( array('name' => 'Jane Doe') );
```
Or alternatively you can call `update` method right away.
```php
User::update( array('name' => 'Jane Doe'), array('id' => 1) );
// The first parameter is the new data, and the second is the "where" condition
```

### Deleting Models
There are several ways to delete model
```php
// Delete retrieved model
$user = User::find(1);
$user->delete();

// Delete a single model by its primary key
User::delete(1);

// Delete multiple models by their primary keys
User::delete(1, 2, 3);
//or
User::delete( array(1, 2, 3) );

// Use Active Record
User::where('status', 1)->delete();
```

## Query Scopes
Scopes is a custom function you can create in your models to generate custom queries

### Defining A Scope
Some conventions:
- Scope method name must be in camel case
- Scope method name must be start with `scope`
- At least one parameter is required. This first parameter is a `QueryBuilder` which you can use to call Active Record methods

```php
class User extends Elegant\Model {
  protected $table = "user";
  
  function scopeActive($query)
  {
    return $query->where('status', 1)->order_by('name');
  }
}
```

### Utilizing Scopes
Using example above, you can do this in your controller
```php
$active_users = User::active()->get();
```
Note that the method name isn't using `scope` prefix.

### Dynamic Scopes
Scopes can also accept parameters to be used in generating queries.
```php
class User extends Elegant\Model {
  protected $table = "user";
  
  // Search an active user by name
  function scopeSearch($query, $keyword)
  {
    return $query->like('name', $keyword)->where('status', 1);
  }
}

// In your controller
$search_results = User::search('John')->get();
```

## Relationship
### One to One
#### Defining One to One Relationship
This the example how to define a one-to-one relationship between a `User` model with `Phone`. In this case, a `User` might have one `Phone`
```php
class User extends Elegant\Model {
  protected $table = "user";
  
  function phone()
  {
    return $this->hasOne('Phone');
  }
}
```

The parameter to `hasOne` method is the name of the related model. Once the relationship set you can retrieve it in you controller this way:
```php
$user = User::find(1);
$phone = $user->phone;
// You can work with $phone object like the usual way

// Returns its property
echo $phone->brand;

// Or updates it
$phone->brand = "Samsung";
$phone->save();
```

Note: the name of the method where you call the `hasOne` isn't have to be the same with the related model name. You can name it anything you want, just make sure it doesn't conflict with exisiting table field name.

In the example above the foreign key in `phone` table is assumed to be `user_id` (lowercased related model's name with `_id` suffix). You can define custom key name as second parameter if your foreign key doesn't match this convention.
```php
$this->hasOne('Phone', 'custom field name');
```

#### Defining the Inverse Relationship
You can also define the inverse of the relationship. For the example after you get a Phone object, you want to know who is the name owner. In the `Phone` model you have to call the `belongsTo` method.
```php
class Phone extends Elegant\Model {
  protected $table = "phone";
  
  function owner()
  {
    return $this->belongsTo('User');
  }
}

// In your controller:
$phone = Phone::find(1);

echo $phone->owner->name;
```
You can also define a custom foreign key as second parameter to `belongsTo` method.

### One to Many
#### Defining One to Many Relationship
An example of one to many relationship is an article can has one or many comments. To define such relationship, you can do this:

```php
class Article extends Elegant\Model {
  protected $table = "article";
  
  function comments()
  {
    return $this->hasMany('Comment');
  }
}
```

The difference between `hasOne` and `hasMany` is the `hasMany` will return an array of matched models, while `hasOne` will return only one model. So in your controller, you should use like this:
```php
$article = Article::find(1);

foreach($article->comments() as $comment)
{
  echo $comment->text;
}
```

#### Defining the Inverse Relationship
As in one to one relationship you can define the inverse relationship between `Comment` and `Article` model.
```php
class Comment extends Elegant\Model {
  protected $table = "comment";
  
  function article()
  {
    return $this->belongsTo('Article');
  }
}

// In controllers
$comment = Comment::find(1);

return $comment->article->title;
```
Again, you can set a custom foreign key to `belongsTo` method as stated in the One to One section.

### Many to Many
#### Defining Many to Many Relationship
Many to many relationship is the most complex relationship. It requires a pivot table to bridge the relation between two models.

An example of this relationship is between an `Article` model with a `Tag` model. An article might has one or more tag while a tag can also be in one on more article.

To define the relationship, you should do this:

```php
class Article extends Elegant\Model {
  protected $table = "article";
  
  function tags()
  {
    return $this->belongsToMany('Tag');
  }
}
```

To retrieve the tags:
```php
$article = Article::find(1);

foreach($article->tags as $tag)
{
  echo $tag->name;
}
```

Or you can do vice-versa in `Tag` model:
```php
class Tag extends Elegant\Model {
  protected $table = "tag";
  
  function articles()
  {
    return $this->belongsToMany('Article');
  }
}

// In controllers
$tag = Tag::find(1);
foreach($tag->articles as $article)
{
  echo $article->title;
}
```

#### Customizing Pivot Table
By default Elegant will assume that the name of pivot table is the concatenated name of two models using underscore in alphabetical order. So if the models are `Article` and `Tag`, the default pivot table name is `article_tag`.

If you want to use another name for the pivot table you can specify in second parameter of the `belongsToMany` method.
```php
// Use a custom pivot table name
$this->belongsToMany('Tag', 'custom pivot name');
```

You can also customize the name of associated keys in the pivot. By default it uses the same convention as in One to One or One to Many. So for the example, in `article_tag` table, the fields will be `article_id` and `tag_id`.

To customize the key name, you can pass third and/or fourth parameter. The third parameter is the associated key of current model, while the fourth is for the related model.

Basicly, this is a `belongsToMany` will look like:
```php
$this->belongsToMany('Tag', 'article_tag', 'article_id', 'tag_id');
```

### Working With Related Model's Record
Let's say an `Article` model might have many `Comment`. But when you querying an article, probably you want to show the approved comments only, which in this case have `status` equals to 1.

You can do it in two ways. The first one is by chaining the relation object with `where` method (or any CodeIgniter Active Record class).
```php
// Using example above
$article = Article::find(1);

$comments = $article->comments()->where('status', 1)->get();
foreach($comments as $comment)
{
  echo $comment->text;
}
```

Note that you need to call the `comments` as a method (`$article->comments()` instead of `$article->comments`). And you will always need to call the `get()` method at the end to finally fetch the records you want.

The second way is by chaining the `hasMany` or `belongsToMany` method right after you define the relationship.
```php
class Article extends Elegant\Model {
  protected $table = "article";
  
  function approvedComments()
  {
    return $this->hasMany('Comment')->where('status', 1);
  }
}
```

This way you can have a cleaner syntax without needs to call `get()` method. In your controllers you can do this:
```php
$article = Article::find(1);

foreach($article->approvedComments as $comment)
{
  echo $comment->text;
}
```

You can call the `approvedComments` as a property to do the loop like example above, or you call it as a method to chain it again with another Active Record method. Just don't forget to call `get()` at the end.
```php
// Show only 5 first comments
$comments = $article->approvedComments()->limit(5)->get();

// Or sort the comments by date
$comments = $article->approvedComments()->order_by('date', 'desc')->get();
```

## Eager Loading
### Using Eager Load
Eager loading is a technique to reduce the number of queries needed to relate one model to another. Now take a look at this model:
```php
class Article extends Elegant\Model {
  protected $table = "article";
  
  function comments()
  {
    return $this->hasMany('Comment');
  }
}
```

Then, for the example we want to show all articles along with their comments. So perhaps you will use this code:
```php
$articles = Article::all();

foreach($articles as $article)
{
  echo $article->title;

  foreach($article->comments() as $comment)
    echo $comment->text;
}
```

There's nothing wrong with that code, except that each time you call the relationship method, (the `comments()` method) to retrieve the comments a new query is built. So imagine if we have 50 articles to be shown, that means the loop will run 51 queries (the 1 is for fetching all articles) like this:

```sql
SELECT * FROM article;

SELECT * FROM comment WHERE article_id = 1;
SELECT * FROM comment WHERE article_id = 2;
SELECT * FROM comment WHERE article_id = 3;
...
```

To solve that problem, Elegant provides the support for eager loading. Now take a look at this code

```php
$articles =  Article::all();
$articles->load('comments');

foreach($articles as $article)
{
  echo $article->title;

  foreach($articles->comments as $comment)
    echo $comment->text;
}
```

It will produce the same output, but with a drastically decrease in the number of queries. Instead of running N + 1 queries like example above, it will run queries like this:
```sql
SELECT * FROM article;
SELECT * FROM comment WHERE article_id IN (1, 2 ,3 ,4 ,5 .....);
```

The secret is the `load()` method. Pass the name of the relation method (`comments`) then it will smartly build the query using `IN` keyword and match each comment to the right article. Note that when fetching the comments using eager load, you should always call it as a property instead of as a method as in the example above (`$articles->comments`, not `$articles->comments()`).

## Accessors and Mutators
Sometimes you want to transform some of your model's value when setting or getting them. The accessors will transform your model value before returning it to your application, so you don't have to do the transformation by yourself each time you need that value. While the mutators will transform it before you save it to the database, freeing yourself from doing it every time you gonna insert a new row.

### Accessors
Example of accessors:
```php
class User extends Elegant\Model {
  protected $table = "user";
  
  function getAttrFirstName($value)
  {
    return strtoupper($value);
  }
}
```

That way, in your controllers every time you echo or use the `first_name` field from a User model, it will always in all capital letter despite the actual value saved.

```php
$user = User::find(1);
echo $user->first_name; // will echo something like 'JOHN'
```

### Mutators
Example of mutators:
```php
class User extends Elegant\Model {
  protected $table = "user";
  
  function setAttrFirstName($value)
  {
    return strtolower($value);
  }
}
```

So when you save a new User or updating an existing one's first name, the value will be transformed into lowercase.

```php
$user = new User;
$user->first_name = 'JOHN';
$user->save();
```

In example above the value will be lowercased upon saving, so the in database the first_name value will be: `john`

### Conventions
When you want to declare a mutator/accessor method, here is some rules you need to understand:
- The methods name needs to in camelCase
- Accessors should be prefixed with `getAttr` while mutators with `setAttr` like in the example above
- Accessors and mutators actually receive two parameters. The first is the value of the field you want to transform, and the second is the model object (optional) in case you want to look for some reference to other field.

Example:
```php
class User extends Elegant\Model {
  protected $table = "user";
  
  function getAttrLastName($value, $model)
  {
    // look up to "gender" field to define salutation to be attached
    $salutation = $model->gender == 'male' ? 'Mr.' : 'Mrs.';
    return $salutation . ' '  . ucwords($value);
  }
}
```
- You may define an accessor for a field that actually doesn't exists in database. For the example in your table you only have `first_name` and `last_name` field. If you want to show a full name, instead of echoing the fields one by one you can do this:

```php
  function getAttrFullName($value, $model)
  {
    // $value will be empty since the field doesn't exist.
    // use another field instead
    return $model->first_name .' '. $model->last_name;
  }
```

## Miscellaneous
### Converting Models to Array / JSON
The Elegant query result is always returned as a special `Elegant\Result` object. If you wish to work with plain array instead, you may convert is using `toArray()` method.

```php
$users = User::all();
return $users->toArray()
```

If you want to include a related model, you can use the eager load method
```php
$articles = Article::all();
articles->load('comments');

return $articles->toArray();
```

You can also convert the models to JSON object instead. Especially if you work with Javascript library like Backbone.js. To do so you can call `json()` method
```php
$articles = Article::all();
articles->load('comments');

echo $articles->json();
```


### Debugging
If you want to debug your application using CI Profiler class, you should define a constant named `ELEGANT_DEBUG` with value `true` in `config/constants.php` file. Otherwise the queries will not show up in the profiling result.
```php
define('ELEGANT_DEBUG', true);
```
