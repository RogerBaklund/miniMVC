<div class="article front">

  <h2>Introduction</h2>
  <p>
    This is a lighweight MVC framework for PHP. 
    It has support for different types of models and use standard PHP files as 
    templates for the views. You can have an <b>unlimited</b> number of models and 
    views mixed on the <b>same page</b>. You can easily have nested views by rendering 
    views into models, into other objects or into associative arrays which you then 
    use in another view.
  </p>
  <p>
    The different components in this framework are <b>loosly coupled</b>, this means 
    for instance you can use the <code>View</code> classes without using the 
    <code>Model</code> classes and vice versa. You can also use the <code>Route</code> 
    class without using controllers. Using the <code>Controller</code> class without
    using oher classes does not make much sense as it offers no functionality.    
  </p>

  <h3>Views</h3>
  <p>
    There are four view classes, <code>View</code>, <code>InlineView</code>,
    <code>StringView</code> and <code>FormatView</code>. The first one is for "normal"
    external templates, and it is also the base class for all the other view classes. 
    The three others are for inline template strings, otherwise they have the same 
    functionality as the base class. <code>View</code> and <code>InlineView</code> 
    supports full PHP tags, the <code>StringView</code> and <code>FormatView</code> 
    classes are for simpler string templates and they do <b>not</b> support full PHP 
    syntax. <code>StringView</code> accepts variables without PHP tags, just with a 
    <code>$</code> prefix and optionally within curly brackets. <code>FormatView</code> is even 
    simpler, it uses <code>%s</code> and other string format specifiers as a 
    placeholder for the variables.
  </p>

  <h3>Models</h3>
  <p>
    The basic <code>Model</code> class is just a holder of runtime data, there are also 
    <code>JSONModel</code>, <code>DBModel</code>, <code>DBStatusModel</code>, 
    <code>DBTableModel</code> and <code>DBRecordModel</code> classes. 
    <code>JSONModel</code> and <code>DBRecordModel</code> represents persistant data, 
    changes to the models are automatically saved by default.
  </p>

  <h3>Controllers</h3>
  <p>
    The <code>Controller</code> class is a base class for controllers. It is very 
    minimalistic, it just have a <code>_Default()</code> method which outputs an
    error message telling you that you must implement your own controller.
  </p>
  <p>
    When you make a controller based application you need a <code>DefaultController</code>
    which should extend <code>Controller</code> and this will serve as a base class for 
    all other controllers in the application. It should also have a <code>showPage()</code> 
    method (or similar, up to you) and possibly a <code>showError()</code> method. 
    These should be inherited by the other controllers and called by your action methods.
  </p>
  <p>
    The controller will normally be instantiated and called from a routing rule. Exactly
    how you map the URI to the controllers and actions is up to you, this demo (index.php)
    contains an example where the URI is interpreted as 
    <code>/{controller}/{action}/{params}</code> and mapped to the method call 
    <code>{controller}Controller::{action}({params})</code> in the file 
    <code>controllers/{controller}Controller.php</code>, but you can modify this to 
    your liking. This is described in more detail on he Controllers page (see menu).
  </p>

  <h3>Routing</h3>
  <p>
    The Route class is for application routing. This class can be used independently 
    of the other classes. It can match the incoming request URI against patterns, and 
    trigger different parts of the application depending on this pattern. When using
    controllers the Route class can be used to map the URI to the correct controller. 
  </p>

  <h3>Utils</h3>
  <p>
    This framework also contains some utility classes which can be usefull when 
    building an application:
  </p>
  <ul>
    <li>FormBuilder: Creating HTML forms </li>
    <li>SimpleMenu: Building menus</li>
    <li>SimpleInput: Getting input from user (simple forms)</li>
    <li>BootstrapUI: Helpers for Twitter Bootstrap development</li>
  </ul>
  <p>
    Choose from the menu on the <a href="#top">top</a> for more information about each class.
  </p>
  <h3>Demo applications</h3>
  <p>
    This distribution contains the following demo applications:
  </p>
  <ul>
    <li><a href="<?=APP_PATH?>db/">Database manager</a></li>
    <li><a href="<?=APP_PATH?>pwiz/">Project wizard</a></li>
    <li><a href="<?=APP_PATH?>bs/">Bootstrap demo</a></li>
  </ul>
</div>