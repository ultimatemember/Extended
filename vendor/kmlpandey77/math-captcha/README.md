# MathCaptcha
A Simple PHP Math Captcha



## Preview
### Math in Image
![A Simple PHP Math Captcha](https://raw.githubusercontent.com/kmlpandey77/MathCaptcha/master/PreviewImage.png "Captcha Preview")

### Math in Text
![A Simple PHP Math Captcha](https://raw.githubusercontent.com/kmlpandey77/MathCaptcha/master/PreviewText.png "Captcha Preview")


## Usage

```
composer require kmlpandey77/math-captcha

```



### Math in Image
It will return Math in image

Create `captcha.php`

```php
<?php
require_once 'vendor/autoload.php'; // link to vendor's autoload.php

use Kmlpandey77\MathCaptcha\Captcha;

$captcha = new Captcha();
$captcha->image();
```

Create `form.php`

```html
<form action="check.php" method="post">
    <p>
        Answer it <img src="./captcha.php" alt="" valign="middle">  <input type="text" name="captcha">
    </p>
    <p><button type="submit" name="submit">Submit</button></p>
</form>
```

### Math in Text
It will return Math in text

Create `form.php`

Place this code to top of `form.php`
```php
<?php
require_once 'vendor/autoload.php'; // link to vendor's autoload.php

use Kmlpandey77\MathCaptcha\Captcha;
?>
```

And place this code in `body`
```html
<form action="check.php" method="post">
    <p>
        Answer it <?php echo new Captcha; ?>  <input type="text" name="captcha">
    </p>
    <p><button type="submit" name="submit">Submit</button></p>
</form>
```


### Check
Checks to see if the user entered the correct captcha key

Create `check.php`

```php
<?php
require_once 'vendor/autoload.php'; // link to vendor's autoload.php
use Kmlpandey77\MathCaptcha\Captcha;

if(isset($_POST['submit'])){

	if(Captcha::check()){

        //valid action

        echo('<font color="green">Answer is valid</font>');
	}else{
		echo('<font color="red">Answer is invalid</font>');
	}
}
```