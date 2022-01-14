let open_menu = false;

function start(){
    setupNavMenu();
}

function setupNavMenu(){
    $(".submenu-title").on('click', function(){
        let submenu = $(this).attr("data-submenu");

        $("ul[data-submenu=" + open_menu + "]").slideUp();

        if(open_menu === submenu){
            open_menu = false;
            return;  
        }

        $("ul[data-submenu=" + submenu + "]").slideDown();
        open_menu = submenu;
    });
}

$(() => {
    start();
});