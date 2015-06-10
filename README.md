> **ALPHA!!!**

# Snubbed

Create Snubs of Zend Framework 2 projects to make IDE integration much tighter

# Installing

* Add `"geeh/snubbed": "dev-master"` to you `composer.json` and `composer update`

# Running

* use `php vendor/bin/snub.php <application config> <default controller class>` - you can leave the parameters blank and it will default to `config/application.config.php` and `Zend\Mvc\Controller\AbstractActionController` which should be good enough for a standard skeleton application based projects
* you'll see the Snubs created in the `.ide` folder in the root of your project

# Using

## Controllers

* update your use statement where you import the basic controller in your controllers to use the Snubbed controllers instead - use the `as` keyword to make it seamless is recommended:

```php
namespace Application\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

class IndexController extends AbstractActionController
{
    public function indexAction()
    {
        return new ViewModel();
    }
}
```

Becomes:

```php
namespace Application\Controller;

use Snub\SnubbedZendMvcControllerAbstractActionController as AbstractActionController;
use Zend\View\Model\ViewModel;

class IndexController extends AbstractActionController
{
    public function indexAction()
    {
        return new ViewModel();
    }
}
```

You'll now have code completion in your controllers, including any controller plugins:

![Screenshot](http://c.hock.in/6c1c35.png)

## Views

Snubbed will also generate view snubs that give you code completion in the view, including your view helpers and view variables for each view. In the `.ide/Snub/View` directory, you'll find a `class_map.php` that will tell you which Snub you should use for each view template. You'll see that it's straightforward, and you'll soon be able to predict the filename.

To use the view Snubs, simply include an `@var` docblock at the beginning of your view file. Assuming your controller is:

```php
class IndexController extends AbstractActionController
{
    public function indexAction()
    {
        return new ViewModel(
            ['form' => new Form()]
        );
    }
}
```

Then in your view (in this case `view\application\index\index.phtml`), simply add the following under an opening `<?php` tag right at the top of the file:

```php
<?php
/* @var \Snub\View\ApplicationControllerIndexIndexAction $this */
?>
```

Obviously, you need to replace the Snub class name with the classname that is suitable for this view file. You'll then get code completion in the view, including the view variables that you set in the action:

![Screenshot](http://c.hock.in/7d8da0.png)

> Note: There are some known problems at the moment that stop variable completion from happening. If, for example, your controller is protected by RBAC or ZfcUser authentication, then Snubbed won't be able to dispatch the controller from the command line, and therefore won't be able to analyise the output.

# Helping

At this moment, the code is terrible, untested, but actually works. If you find there are errors in using this then **please** submit issues here so I can try and get them fixed. **PLEASE PLEASE PLEASE**
