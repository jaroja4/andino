$(document).ready(function () {
    //Validator.js
    var validator = new FormValidator({ "events": ['blur', 'input', 'change'] }, document.forms[0]);
    $('#frmLogin').submit(function(e){
        e.preventDefault();
        var validatorResult = validator.checkAll(this);
        if (validatorResult.valid)
            Login();    
        return false;
    });

    // on form "reset" event
    document.forms[0].onreset = function (e) {
      validator.reset();
    }
});

function Login(){
    $.ajax({
        type: "POST",
        url: "class/Usuario.php",
        data: {
            action: 'Login',               
            username:  $("#username").val(),
            password: $("#password").val(),
            beforeSend: function(){
                 $("#error").fadeOut();
            } 
        }        
    })
    .done(function( e ) {
        var data= JSON.parse(e);
        if(data.status=='login'){
            if(data.url)
                location.href= data.url || 'Dashboard.html';
        }
        else if(data.status=='inactivo')
            $("#error").fadeIn(500, function(){
                $("#error").html(`                    
                    <div class="alert alert-danger alert-dismissible fade in" role="alert">
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">×</span></button>
                        Usuario <strong>INACTIVO</strong>.
                    </div>
                `);
            });  
        else if(data.status=='noexiste')
            $("#error").fadeIn(500, function(){
                $("#error").html(`                    
                    <div class="alert alert-danger alert-dismissible fade in" role="alert">
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">×</span></button>
                        Usuario <strong>NO EXISTE</strong>, favor registrarse.
                    </div>
                `);
            });  
        else
            $("#error").fadeIn(500, function(){      
                $("#error").html(`                    
                    <div class="alert alert-danger alert-dismissible fade in" role="alert">
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">×</span></button>
                        Usuario / Contraseña <strong>Inválidos</strong>.
                    </div>
                `);
            });        
    })    
    .fail(function( e ) {
        showError(e);
    });
};

function showError(e) {
    //$(".modal").css({ display: "none" });  
    var data = JSON.parse(e.responseText);
    swal({
        type: 'error',
        title: 'Oops...',
        text: 'Algo no está bien (' + data.code + '): ' + data.msg, 
        footer: '<a href>Contacte a Soporte Técnico</a>',
    })
};