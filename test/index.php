<?php
$x = '10';

?>
<HTML>

<head>
<title>Hello world</title>
<link rel="stylesheet" type="text/css" href="main.css" />
<script type="text/javascript" src="main.js" />
<script type="text/javascript">
    
</script>
</head>

<body>
<div class="intro" id="zaina">
<img src="../images/facebook.png"/>
    <h1>
        Hello World! 
        
    </h1>
    <p>dij;osdjg;fdjgljfd'sgjfd'gk'fdkgsjfd'pgj'hd</p><br/>
    <p>dij;osdjg;fdjgljfd'sgjfd'gk'fdkgsjfd'pgj'hd</p>
</div>

<p> hello <b>world</b>. hi</p>
<p> hello world<b>. hi</b></p>

<div class="intro" id="ghadeer">
<button id="hi">اهلا وسهلا</button>
    <h1>happy 2018</h1>
    <h2>i love you medo</h2>
    <p>for info visit<a href="http://www.google.com">google</a></p>
</div>
<div class="blue-colored">
    <h2>al quds rose </h2>


    <label>
        Hello World!
    
        <?php echo $x ?>
    </label>

</div>

<form action="login.html" method="post" class="blue-colored">
    <input type="text" name="username" placeholder="enter gbhjk" />
    <input type="password" name="pwd" placeholder="enter gbhkojk" />
    <button type="submit">Login</button>

</form>

<h1>
    <a href="www.google.com">Go to google!</a>
</h1>
<input type="hidden" id="data"></input>
<input type="text" id="salma" placeholder="tag"></input>
<input type="text" id="zaina" placeholder="attributes"></input>
<input type="text" id="ghadeer" placeholder="usage"></input>
<button id="addtag">Add tag</button>

<div class="blue-colored">
    General Attributes:
    <br/> class
    <br/> name
    <br/> id
    <br/> style
    <br/>
</div>

<hr>

<table class="table-basic" id="najjar">

    <tr>
        <th>Main HTML tags</th>
        <th>Attributes</th>
        <th>Use</th>
    </tr>
    <!-- See the below tags  !-->
    <tr>
        <td>html</td>
        <td>lang="en"</td>
        <td>indicator of HTML document start</td>
    </tr>
    <tr>
        <td>title</td>
        <td></td>
        <td>Document title in the browser tab</td>
    </tr>
    <tr>
        <td>link</td>
        <td>href, rel, type,</td>
        <td>Linking external documents, mainly CSS files</td>
    </tr>
    <tr>
        <td></td>
        <td></td>
        <td></td>
    </tr>
    <tr>
        <td>meta</td>
        <td>main, content, charset</td>
        <td>Define main charchteristics of the documents (keys used, page description ..etc)</td>
    </tr>
    <tr>
        <td></td>
        <td></td>
        <td></td>
    </tr>
    <tr>
        <td>style</td>
        <td></td>
        <td>equivalent to CSS</td>
    </tr>
    <tr>
        <td>script</td>
        <td></td>
        <td>Linking JS scripts and other types of script, OR writing the script directly inside the tags</td>
    </tr>
    <tr>
        <td></td>
        <td></td>
        <td></td>
    </tr>
    <tr>
        <td>head /body/ footer</td>
        <td></td>
        <td>3 components of each documents, you link CSS/JS and creating top bar in the head, using body to present the main
            content, and using footer to show bottom bar if applicable</td>
    </tr>
    <tr>
        <td></td>
        <td></td>
        <td></td>
    </tr>
    <tr>
        <td>section</td>
        <td></td>
        <td>main component within body</td>
    </tr>
    <tr>
        <td>aside</td>
        <td></td>
        <td>for the side containers (like ADs)</td>
    </tr>
    <tr>
        <td>div</td>
        <td></td>
        <td>container for other elements, allows for more flexability to present elements togather</td>
    </tr>
    <tr>
        <td></td>
        <td></td>
        <td></td>
    </tr>
    <tr>
        <td>p </td>
        <td></td>
        <td>paragraph</td>
    </tr>
    <tr>
        <td>h1 - h6</td>
        <td></td>
        <td>Headings / Topics</td>
    </tr>
    <tr>
        <td>label</td>
        <td></td>
        <td>inline text</td>
    </tr>
    <tr>
        <td>b</td>
        <td></td>
        <td>Bold</td>
    </tr>
    <tr>
        <td>br</td>
        <td></td>
        <td>New line / Enter</td>
    </tr>
    <tr>
        <td>hr</td>
        <td></td>
        <td>Horizontal line</td>
    </tr>
    <tr>
        <td>a</td>
        <td>href</td>
        <td>Link</td>
    </tr>
    <tr>
        <td>span</td>
        <td></td>
        <td>inline part of paragrapher OR character, symbol, or icon With different styling</td>
    </tr>
    <tr>
        <td>ul/li/ol</td>
        <td></td>
        <td>Listing/Bullets</td>
    </tr>
    <tr>
        <td>img</td>
        <td>src</td>
        <td>Image</td>
    </tr>
    <tr>
        <td></td>
        <td></td>
        <td></td>
    </tr>
    <tr>
        <td>table</td>
        <td></td>
        <td>Table</td>
    </tr>
    <tr>
        <td>tr/th/td</td>
        <td></td>
        <td>Table Rows, Headers, Cells</td>
    </tr>
    <tr>
        <td></td>
        <td></td>
        <td></td>
    </tr>
    <tr>
        <td>form</td>
        <td>method, action</td>
        <td>for applications such Register, Login, purchase, payment ...etc,</td>
    </tr>
    <tr>
        <td>input</td>
        <td>type(text, textarea, button, email,password...etc), value, placeholder, enabled, autocomplete ...etc,</td>
        <td>inputs for Forms</td>
    </tr>
    <tr>
        <td>button</td>
        <td>type(button, reset, submit), disabled, autofocus</td>
        <td>Buttons-mainly for forms</td>
    </tr>

</table>
</body>

</HTML>
