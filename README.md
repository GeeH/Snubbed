# Snubbed

Create Snubs of Zend Framework 2 projects to make IDE integration much tighter

# Installing

* Add `"geeh\snubbed": "dev-master"` to you `composer.json` and `composer update`

# Running

* use `php vendor/bin/snub.php <application config> <default controller class>` - you can leave the parameters blank and it will default to `config/application.config.php` and `Zend\Mvc\Controller\AbstractActionController` which should be good enough for a standard skeleton application based projects
* you'll see the Snubs created in the `.ide` folder in the root of your project

# Using

## Controllers

* update your use statement where you import the basic controller in your controllers to use the Snubbed controllers instead - use the `as` keyword to make it seamless is recommended:

```
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

```
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
