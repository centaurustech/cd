<form id="searchform" method="get" action="<?php bloginfo('home'); ?>/">
        <input type="text" value="Search: type and hit enter!" onfocus="if (this.value == 'Tapez votre recherche ici!') {this.value = '';}" onblur="if (this.value == '') {this.value = 'Tapez votre recherche ici';}"  name="s" id="s" />
        <input type="submit" class="hidden" id="searchsubmit"  />
</form>
