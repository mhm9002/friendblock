<section id="main_section">
	<section id="wrapper">
        <div id="register-wrapper">
			
                        
                <form id="artForm" method="post" action="" enctype="multipart/form-data">
                    <div class="fieldset">
                        <label for="image">Select image</label>
                        <input class="input row" type="file" name="image" id="image">
                    </div>
                    <div class="fieldset" >
                        <label for="lettersUsed">Select letters to use for the output image (for custom Enter from darkest to lightest) </label><br>
                        <input class="lu" type="radio" name="lettersUsed" id="1" value="For detailed image">For detailed image<br>
                        <input class="lu" type="radio" name="lettersUsed" id="2" value="For image with high contrast" checked>For image with high contrast<br>
                        <input class="lu" type="radio" name="lettersUsed" id="3" value="Custom">Custom<br>
                        <input class="input row" type="text" id="lettersUsed" name="lettersUsed" value="@#+. ">
                    </div>
                    <div class="fieldset">
                        <label for="m">Enter either Pixels per letter OR Letters per row(recommended) </label><br>
                        <input type="radio" name="m" id="m1" value="Pixels per letter">Pixels per letter<br>
                            <input class="input" type="text" id="pixelsPerLetter" name="pixelsPerLetter" value="20"><br>
                        <input type="radio" name="m" id="m2" value="Letter per row" checked>Letter per row<br>
                            <input class="input" type="text" id="lettersPerRow" name="letterPerRow" value="100">
                    </div>
                    <div class="fieldset">
                        <label for="c">Select output colors (recommended: greyscale on white) </label><br>
                        <input type="radio" name="c" id="1" checked>greyscale on white background<br>
                        <input type="radio" name="c" id="2" >greyscale on black background<br>
                        <input type="radio" name="c" id="3" >black on white background<br>
                        <input type="radio" name="c" id="4" >white on black background<br>
                        <input type="radio" name="c" id="5" >colorful on white background<br>
                        <input type="radio" name="c" id="6" >working with font weight and mixed background (artistic but slow)<br>
                    </div>

                    <input class="btn btn-primary" type="submit" value="Submit">

                    <?php render_loading_wrapper() ?>
                </form>

</div></section></section>