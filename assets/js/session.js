var session=  {
    state: undefined,
    check(){
        session.state=undefined
        $.ajax({           
            type: "POST",
            url: "class/usuario.php",
            data: {
                action: 'CheckSession',
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
                    session.setUsername(data.username, data.nombre);
                    session.setMenu(data.eventos);  
                    session.state=true;
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
    }
}