# Frontend Dev Styleguides #

#### WORKFLOW

We will start off with the workflow.

The root directory of the frontend is /frontend-src.
It contains other sub-directories that rapresents the template we are working on.

For example frontend-src/standard, it means that we are working on the
standard template, and so on. 

Before starting working on a new feature/fix it is **REQUIRED** 
to be **ALIGNED** to the Formalms Repository.


##### SOME IMPORTANT RULES

- **ALWAYS BE SURE THAT YOU ARE FOLLOWING THE WORKFLOW, ALWAYS CHECK, DOUBLECHECK AND EVEN TRIPLECHECK IF IT IS NECESSARY**
- Be sure to not modify compiled files
(main.css / main.js), but to work only on scss/js files inside the static folder.


#### PURPLE NETWORK STYLEGUIDES

Our styleguides can be found here: 

- https://github.com/purplenetwork/style-guides

Follow them.

 
#### HTML

Since we are restyling an already completed site, 
we are working with single logic blocks, one at the time.

To serve this purpose we are using BEM methodology.

Block - Element - Modifier is a naming convention used to give a better 
understanding of the relationship between HTML and CSS.

A **BLOCK** is a top-level abstraction of a component that should be thought of as a parent, a standalone entity that is meaningful
on its own.
Block can be nested but they remain equal, there is no hierarchy between blocks.

For example we have a button:

```
<div class="block">
    ...
</div>
```

Then we have the **ELEMENT.** An element is a part of a block that doens't have any standalone meaning, this means
that an element is semantically tied to its block. 
Elements can contain other elements but not blocks.
It's good practice not to nest more than two direct childs.
<br>
The css class is formed by adding two underscores after the block name:

```
<div class="block">
    <div class="block__element"></div>
</div>
``` 

Last we have the **MODIFIER.** A modifier is flag that can be set on block or elements. It's used to change the appearance,
behavior or state.

```
<div class="block block--red">
    <div class="block__element block__element--disabled"></div>
</div>
``` 

What follows is an example form:


```
<form class="form form--theme-xmas form--simple">
  <input class="form__input" type="text" />
  <input
    class="form__submit form__submit--disabled"
    type="submit" />
</form>
```

    
More on BEM:

- http://getbem.com/introduction/
- https://css-tricks.com/bem-101/

#### CSS/SCSS

To style the elements we are using SCSS, this is a brief introduction.

We have a main entry point and its the main.scss file.
Here you import all the scss partial files. This it the one we are using:


```
//EXTERNAL LIBRARIES
@import '../../node_modules/breakpoint-sass/stylesheets/breakpoint';

//CONFIG
@import 'config/config';
@import 'font-awesome/font-awesome';

//UTILS
@import 'utils/fonts';
@import 'utils/helpers';
@import 'utils/icons';
@import 'utils/grid';

//COMPONENTS
@import 'components/router';
@import 'components/typography';
@import 'components/buttons';
@import 'components/inputs';
@import 'components/carousel';
@import 'components/forma-tooltip';
@import 'components/tabnav';

//MODULES
@import 'modules/layout';
@import 'modules/course-box';
@import 'modules/fake-grid';
@import 'modules/tab-menu';
@import 'modules/footer';
@import 'modules/top-menu';
@import 'modules/user-panel';
@import 'modules/social-login';
@import 'modules/select-language';
@import 'modules/aside';

//PAGES
@import 'pages/homepage';
```

We import **EXTERNAL LIBRARIES**, **CONFIG**, **UTILS**, **COMPONENTS**, **MDULES** and **PAGES.**

###### EXTERNAL LIBRARIES

Those are external libraries used while developing, like the sass breakpoint we have imported. 

###### CONFIG

In the config.scss we declare all the variables we need, for example:


```
$dark-orange: rgba(200, 64, 0, 1);
$orange: rgba(255, 108, 0, 1);
$orange--hover: rgba(255, 108, 0, .5);
```

Those variables are used for colours. 
In scss you can declare a variable like this: 

- $variable-name: some-value;

###### UTILS

Utils can be anything it's useful for developing until it's not an external library.

###### COMPONENTS

Components are small reausable blocks like buttons, inputs, cta and so on.
If they are combined together they can form a module.

Let's take for example the code inside _buttons.scss:

```    
.forma-button {
  display: inline-block;
  width: 100%;
  padding: 14px 20px;
  min-height: 40px;
  text-align: center;
  text-decoration: none;
  text-transform: uppercase;
  text-shadow: none;
  line-height: 12px;
  color: $white;
  background: $dark-orange;
  font-family: $font;
  font-size: 11px;
  border: none;
  border-radius: 5px;
  outline: none;
  box-shadow: none;
  cursor: pointer;
  transition: background-color 0.4s ease 0s;
    
  @include breakpoint($tablet) {
    font-size: 11px;
  }
    
  @include breakpoint($desktop) {
    font-size: 13px;
  }
}
    
.forma-button--green {
  background: $green;
}
    
.forma-button__label {
  position: relative;
  display: inline-block;
  color: inherit;
  font: inherit;
  text-decoration: inherit;

  &:after {
    content: '\f101';
    font-family: $icon;
    font-size: inherit;
    color: rgba(255, 255, 255, 0.7);
    margin-left: 6px;
  }
}

```

In the previous code we have:
- the BLOCK (.forma-button)
- the ELEMENT (.forma-button__label)
- the MODIFIER (.forma-button--green) 

###### MODULES

Modules can contain one or more components

```
<div class="course-box">
  <div class="course-box__item">
    <div class="course-box__title">Lorem ipsum dolor sit amet</div>
  </div>
  <div class="course-box__item course-box__item--no-padding">
    <div class="course-box__img">
      <img src="" alt="">
      <div class="course-box__img-title">lorem ipsum</div>
    </div>
  </div>
  <div class="course-box__item">
    <div class="course-box__desc">
      Lorem Ipsum dolor sit amet, consectetur adipiscing elit. Facilis ponatur
      infinito oderis obruamus. Effectices, terroribus cognosci elegans totam
      atilli arare p minuendas.
    </div>
  </div>
  <div class="course-box__item">
    <div class="course-box__date-box calendar-icon--check">31 agosto 2016</div>
    <i class="fa fa-angle-right" aria-hidden="true"></i>
    <div class="course-box__date-box course-box__date-box--end calendar-icon--green-cross">30 novembre 2017</div>
  </div>
  <div class="course-box__item" id="">
    <a class="forma-button forma-button--orange-hover forma-button--full" href="">
      <span class="forma-button__label">
        Entra nel corso
      </span>
    </a>
  </div>
</div>
```

In the previous code we have defined the module .course-box that contains
the previously defined buttons .forma-button

###### PAGES

Last but not least we have Pages, here we can define page specific
styles like for example the _homepage.scss file:


```
.homepage {
  height: 100%;
  overflow: auto;
  position: relative;
  background: {
    image: url('#{$assets}images/login/bg.jpg');
    size: cover;
    position: 50% 50%;
    repeat: no-repeat;
    color: $white;
  }
}
    
.homepage__footer {
  padding: 0;
  margin: 30px auto;
  text-align: center;
  color: $white;

  a {
    color: $white;
  }
}
```
    
***Remember to import all your files in the main.scss***


#### JAVASCRIPT

We have components and modules in javascript too. They work in the same logic of the scss ones.


To add javascript code that is available to the scope of the whole project simply add it to
the file *allpages.js*

If you need page specific js then you add your js-class to the config.js file like this example:

```
'use strict';
    
var Pages = {
	index: '.js-router--home',
	example: '.js-router--example',
	'test/one': '.js-router--test-one',
	'homepage': '.js-router--homepage'
};
    
module.exports = Pages;
```

In the previous code we created an entry in the Pages object so that 
whenever the .js-router--homepage class is in the DOM
the homepage.js file is loaded and with him all its code.

The object keys are the file names, their values are the js-classes that triggers them.

So for example to add another page we create the js file exampletwo.js under pages/ and add this line to the config.js

```
'use strict';
    
var Pages = {
	index: '.js-router--home',
	example: '.js-router--example',
	'test/one': '.js-router--test-one',
	'homepage': '.js-router--homepage',
	'exampletwo': '.js-router--exampletwo'
};
    
module.exports = Pages;
```

Now whenever js-router--exampletwo is loaded into the DOM the router loads the js too.


### Build

In order to prepare assets to be used/included in the project, a build process is required.
It provides code compilation and optimizations like concatenation, minification, autoprefixing and more.

Run the following command whenever a source code modification is introduced:

```
npm run build
```

After execution, a "static" folder will be created in html/templates/{template}/ and will contain all
compiled frontend files for {template}.

NB: the compiled files should never be edited manually as they will be overwritten each time a build process
is executed.


### Note on styleguide creation

This is the command to execute:


```
npm run styleguide
```

Let's take as example the course-box styleguide:


```
// Course Box
//
// These are the restyled course boxes<br />
//
// Style guide: 3

// My courses
//
// This is the restyled course box used in My Courses<br />
// <strong>NB: The 'style' attributes are just for this representation only, when implementing
// be sure to delete them.</strong><br />
//
// Markup:
//  <div style="max-width: 404px;">
//    <div class="course-box">
//      <div class="course-box__item">
//        <div class="course-box__title course-icon--active">Lorem ipsum dolor sit amet</div>
//      </div>
//      <div class="course-box__item course-box__item--no-padding">
//        <div class="course-box__img">
//          <img src="" alt="">
//          <div class="course-box__img-title">lorem ipsum</div>
//        </div>
//      </div>
//      <div class="course-box__item">
//        <div class="course-box__owner course-box__owner--5">Amministratore</div>
//        <div class="course-box__desc">
//          Lorem Ipsum dolor sit amet, consectetur adipiscing elit. Facilis ponatur
//          infinito oderis obruamus. Effectices, terroribus cognosci elegans totam
//          atilli arare p minuendas.
//        </div>
//      </div>
//      <div class="course-box__item course-box__item--half course-box__item--no-padding">
//        <div class="course-box__date-text">31 agosto 2016</div>
//      </div>
//      <div class="course-box__item course-box__item--half course-box__item--no-padding">
//        <a class="forma-button forma-button--orange-hover forma-button--full" href="">
//          <span class="forma-button__label">
//            Entra nel corso
//          </span>
//        </a>
//      </div>
//    </div>
//  </div>
//
// Style guide: 3.1
```

You need to use the double slashes when writing the styleguide.
The previous code is found in the _course-box.scss file, so every file has its own
styleguide.

More on kss syntax:

- http://warpspire.com/kss/syntax/