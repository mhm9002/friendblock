<div id="main_searchbar">
    <div class="search-keywordbar"><?php echo $keyword; ?></div>
    <div class="col-sm-12 tab-bar">
        <ul class="nav-tab-ul">    
            <li class="tab <?php echo $tab=='people'? ' tmp':'' ?>" data-whatever="#searchPeople">
                <label>People</label>
            </li>
            <li class="tab <?php echo $tab=='tags'? ' tmp':'' ?>" data-whatever="#searchTags">
                <label>Tags</label>
            </li>
        </ul>
    </div>
</div>
