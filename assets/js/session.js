var session=  {
    state: undefined,
    check(){
        session.state=undefined
        $.ajax({           
            type: "POST",
            url: "class/usuario.php",
            data: {
                action: 'checkSession',
                url: window.location.href,
                // success: function(data) {
                //     return data;
                // }
            }
        })
        .done(function( e ) {
            var data= JSON.parse(e);
            switch(data.status){
                case 'login':
                    $('.right_col').show();
                    session.setUsername(data.email, data.nombre);
                    session.setMenu(data.eventos);  
                    session.state=true;
                    //session.sideBarDraw(data);
                    $(".main_container").removeAttr("style");
                    break;
                case 'nocredencial':
                    $('.right_col').hide();
                    session.setUsername(data.username, data.nombre);      
                    session.setMenu(data.eventos);    
                    session.state=false;
                    swal({
                        //position: 'top-end',
                        type: 'error',
                        title: 'El usuario no tiene credenciales para ver esta página.',
                        showConfirmButton: false,
                        timer: 3000
                    });
                    break;
                case 'invalido':
                    session.state=false;
                    location.href= 'login.html'; 
                    break;
            }   
        })    
        .fail(function( e ) {        
            showError(e);
            location.href= 'login.html';
        });
    },
    in(e){
        if(e.code==401){
            swal({
                //position: 'top-end',
                type: 'warnig',
                title: 'Sesión Expidada',
                text: e.msg,
                showConfirmButton: false,
                timer: 3000
            });
            session.state=false;
        location.href= 'login.html'; 
        }        
    },
    setUsername(un, n){
        $('#call_username').html(
            '<img src="images/user.png" alt="" > ' + un+ ' ' + 
            '<span class=" fa fa-angle-down" ></span> '        
        );
        $('#call_name').text(n);
    },
    setMenu(eventos){
        $('#call_menu').html('');
        $.each(eventos, function (i, item) {
            $('#call_menu').append(`
                <li>
                    <a href="${item.url}">${item.nombre}</a>
                </li>
            `);
        });
    },  
    end(){
        $.ajax({
            type: "POST",
            url: "class/usuario.php",
            data: {
                action: 'endSession'
            }
        })
        .done(function( e ) {
            location.href= 'login.html';
        })    
        .fail(function( e ) {        
            showError(e);
            //location.href= 'login.html';
        });
    }, 
    sideBarDraw(dataMenu) {

        if ( $("#sidebar-menu").length ) {

            $("#sidebar-menu").empty();
    
            var menu_section =
                `<div class="menu_section">
                <h3>${dataMenu.bodega}</h3>
                <ul id="menu" class="nav side-menu">
                    
                </ul>
            </div>`;
            $("#sidebar-menu").append(menu_section);
    
            $.each(dataMenu.eventos, function (i, item) {
                if ($('#' + item.menuPadre).length) {
                    if(!$('#' + item.id).length){
                    var link =
                        ` <li id="${item.id}"><a href="${item.url}">${item.nombre}</a></li>`;
                    $("#list_" + item.menuPadre).append(link);
                    }
                } else {
                    var menu =
                        `<li id="${item.menuPadre}" ><a><i class="${item.icono}"></i> ${item.menuPadre} <span class="fa fa-chevron-down"></span></a>
                            <ul id="list_${item.menuPadre}" class="nav child_menu">
                                <li id="${item.id}"><a href="${item.url}">${item.nombre}</a></li>
                            </ul>
                        </li>`;
                    $("#menu").append(menu);
                }
            });  
            //
            if (typeof init_sidebar === "function") 
                init_sidebar();
            else {
                setTimeout(function(){
                    Session.sideBarDraw(dataMenu);               
                 }, 500);                
            }
        }
    }

}