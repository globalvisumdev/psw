<?php require('estructura/header.php') ?>
<link rel="stylesheet noopener" href="../../modulos/toastNotification/toastNotification.css">


<title>Nuevo Usuario</title>
</head>
<style>
    .divHeader {
        display: flex;
    }
</style>

<body>
    <div class="toastDiv hidden">
        <div class="toast-content">
            <i id="logoOperacion" class="fa-solid check"></i>
            <div class="message">
                <span class="text text-1" id="estadoOperacion"></span>
                <span class="text text-2" id="mensajeOperacion"></span>
            </div>
        </div>
        <i class="fa-solid fa-xmark closeIcon"></i>
        <div class="progress"></div>
    </div>

    <div  class="container animate__animated animate__fadeInDown animate__faster" id="contenido">
        <div class="divHeader container">
            <div class="header-section">
                <h2 class="title">Nuevo <span>Usuario</span></h2>
            </div>
            <div class="circCont">
                <button class="circle boxShadow" data-animation="fadeOut" data-remove="3000"
                    onclick="redirect()"></button>
            </div>
        </div>

            <form class="row g-2 needs-validation formulario animate__animated animate__fadeIn" id="formulario" novalidate="">
                <?php 
                session_start();
                    if ($_SESSION ['myHierarchy'] == 1) {
                        ?>
                            <div class="col-md-12 position-relative" id="grupocliente">
                                <label for="cliente" class="form-label">Cliente</label>
                                <select class="form-select" id="cliente_id" required="" autocomplete="nope">
                                    <option selected="" disabled="" value=""> Seleccione un cliente</option>
                                </select>
                            </div>
                        <?php
                    }
                ?>
                <div class="col-md-6 position-relative" id="grupoapellido">
                    <label for="apellido" class="form-label">Apellido</label>
                    <input type="text" class="form-control" id="lastname" required="" autocomplete="nope">
                </div>
                <div class="col-md-6 position-relative" id="gruponombre">
                    <label for="nombre" class="form-label">Nombre</label>
                    <input type="text" class="form-control" id="firstname" required="" autocomplete="nope">
                </div>
                <div class="col-md-6 position-relative" id="grupousuario">
                    <label for="usuario" class="form-label">Nombre de Usuario</label>
                    <input type="text" class="form-control" id="username" required="" autocomplete="nope">
                </div>
                <div class="col-md-6 position-relative" id="grupogrupo">
                    <label for="grupo" class="form-label">Grupo</label>
                    <select class="form-select" id="groupid" required="" autocomplete="nope" >
                        <option selected="" disabled="" value="">Seleccionar grupo</option>
                    </select>
                </div>

                <div class="col-md-6 position-relative" id="grupocontraseña">
                    <label for="contraseña" class="form-label">Contraseña</label>
                    <input type="password" class="form-control" id="newpassword" required="" autocomplete="nope">
                </div>
                <div class="col-md-6 position-relative" id="grupoconfirmarContraseña">
                    <label for="confirmarContraseña" class="form-label">Confirmar contraseña</label>
                    <input type="password" class="form-control" id="confirmpassword" required="" autocomplete="nope">
                </div>
                <div class="col-md-4 position-relative" id="grupoFechaInicioReporte">
                    <label for="date" class="form-label">Fecha Inicial para reportes</label>
                    <input type="date" class="form-control" id="fecha_desde_reporte">
                </div>
                
                <div class="col-md-4 position-relative" id="grupoFechaInicioValidez">
                    <label for="date" class="form-label">Fecha inicial de Validez</label>
                    <input type="date" class="form-control" id="validez_desde">
                </div>
                
                <div class="col-md-4 position-relative" id="grupoFechaFinValidez">
                    <label for="date" class="form-label">Fecha final de Validez</label>
                    <input type="date" class="form-control" id="validez_hasta">
                </div>
                <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                    <button class="btn btn-danger" type="reset" onClick="redirect()">Cancelar</button>
                    <button class="btn btn-primary" type="submit" onclick="agregarUsuario()">Confirmar</button>
                </div>
            </form>
    </div>

    <?php require('estructura/footer.php') ?>
  <script src="../../modulos/toastNotification/toastNotification.js" ></script>

<script> 

    function cargarSelects(){

        let dataBase = ["groups_cmd.php",
            <?php 
                if ($_SESSION ['myHierarchy'] == 1) {
            ?>
                "accounts_cmd.php"
            <?php
                }
            ?>
        
        ];
        let selectId = ["groupid","cliente_id"];
        let idOption = ["groupid", "cliente_id"];
        let nameOption = ["groupname", "cliente_nombre"];
        
        for (let i = 0; i < dataBase.length; i++) {
            fetch(dataBase[i],{
                method:'POST',
                headers:{
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `cmd=cargarTabla&form_AccountsForm[newAccount]=true`
            })
            .then( peticion => peticion.json() )
            .then( res=>{
                if (res.redirectPage) {
                    redirect(res.redirectPage);
                    return
                }
                if (res.ok) {
                    let objetoDatos = res.data;

                    cargarOpcionesSelect(selectId[i], objetoDatos, idOption[i], nameOption[i]);
                }
                else{
                    // Mensaje de error de la base de datos
                }
            })
            .catch(e => {
                toastNotification({ok: false, errorMsg: "Ha ocurrido un error de conexión."});
            })
        }
        
    }

    function agregarUsuario(){
        let formNewAccount = document.getElementById("formulario");

        formNewAccount.addEventListener("submit", (e) => {
            e.preventDefault();
        })

        let inputs = document.querySelectorAll('#formulario input');
        let selects = document.querySelectorAll('#formulario select');
        let bodyValue= `cmd=agregarUsuario`;
        
        inputs.forEach(input => {
            bodyValue += `&form_AccountsForm[${input.id}]=${input.value}`
        });

        selects.forEach(select => {
            bodyValue += `&form_AccountsForm[${select.id}]=${select.options[select.selectedIndex].value}`
        });
        
        fetch("accounts_cmd.php",{
            method:'POST',
            headers:{
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: bodyValue
        })
        .then( peticion => peticion.json() )
        .then( res=>{

            if (res.redirectPage) {
                redirect(res.redirectPage);
                 return
            }
            toastNotification(res);

            if (res.ok) {
                limpiarFormulario();
            }
    
             
        })
        .catch(e => {
            toastNotification({ok: false, errorMsg: "Ha ocurrido un error de conexión."});
        })

    }



    window.onload = ()=>{
    cargarSelects();
    }
    
    function redirect() {
        let contenido = document.getElementById("contenido");
        contenido.classList.replace('animate__fadeInDown','animate__fadeOutUp');


        setTimeout(() => {
            history.go(-1);
            setTimeout(() => {
                contenido.classList.replace('animate__fadeOutUp','animate__fadeInDown');
            }, 500);
        }, 600);

    }
</script> 
</body>
</html>