<?php require('estructura/header.php') ?>
<link rel="stylesheet noopener" href="../../modulos/toastNotification/toastNotification.css">
<link rel="stylesheet noopener" href="../../modulos/accordion/accordion.css">

<title>Modificar Usuario</title>
</head>
<style>
    .inputError{
        display: none;
    }

    .invalid-tooltip{
        display: block;
    } 
    th.columnTitle{
        width: 48%;
    }

    tr{
        text-align: center;
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
        <div class="circCont">
            <button class="circle boxShadow" data-animation="fadeOut" data-remove="3000" onclick="redirect()"></button>
        </div>
        <div class="header-section">
            <h2 class="title">Modificar <span>Usuario</span></h2>
        </div>
        <form class="row g-2 needs-validation formulario animate__animated animate__fadeIn" id="formulario" novalidate="">
            <div class="col-md-12 position-relative" id="grupocliente">
                <label for="cliente" class="form-label">Cliente</label>
                <select class="form-select" id="cliente_id" required="" autocomplete="nope">
                    <option selected="" disabled="" value=""> Seleccione un cliente</option>
                </select>
                <!-- <div class="inputError">Debe seleccionar una opción.</div> -->
            </div>
            <div class="col-md-5 position-relative" id="grupoapellido">
                <label for="apellido" class="form-label">Apellido</label>
                <input type="text" class="form-control" id="lastname" required="" autocomplete="nope">
                <div class="inputError">No se permiten caracteres especiales o campo vacío.</div>
            </div>
            <div class="col-md-5 position-relative" id="gruponombre">
                <label for="nombre" class="form-label">Nombre</label>
                <input type="text" class="form-control" id="firstname" required="" autocomplete="nope">
                <div class="inputError">No se permiten caracteres especiales o campo vacío.</div>
            </div>
            <div class="col-md-2 position-relative" id="grupoiniciales">
                <label for="iniciales" class="form-label">Iniciales</label>
                <input type="text" class="form-control" id="initials" required="" autocomplete="nope">
                <div class="inputError">No se permiten caracteres especiales o campo vacío.</div>
            </div>

            <!-- <div class="col-md-2 position-relative" id="grupoblanquear">
                <input class="form-check-input" type="checkbox" value="" id="flexCheckDefault">
                <label class="form-check-label" for="flexCheckDefault">
                    Blanquear Contraseña
                </label>
            </div> -->
            <div class="col-md-5 position-relative" id="grupousuario">
                <label for="usuario" class="form-label">Nombre de Usuario</label>
                <input type="text" class="form-control" id="username" required="" autocomplete="nope">
                <div class="inputError">El usuario tiene que ser de 4 a 16 dígitos y solo puede contener numeros, letras y guion bajo.</div>
            </div>
            <div class="col-md-5 position-relative" id="grupocontraseñaAnterior">
                <label for="contraseñaAnterior" class="form-label">Contraseña Anterior</label>
                <input type="password" class="form-control" id="oldpassword" required="" autocomplete="nope">
                <div class="inputError">La contraseña tiene que ser de 4 a 12 dígitos.</div>
            </div>
            <div class="col-md-2 position-relative" id="grupoblanquear">
                <label for="blanquear" class="form-label">Blanquear</label>
                <select class="form-select" id="blanquear" required="" autocomplete="nope">
                    <!-- <option selected="" disabled="" value="">Seleccione</option> -->
                    <option value="0" selected="">NO</option>
                    <option value="1" >SI</option>
                </select>
            </div>

            <div class="col-md-6 position-relative" id="grupocontraseña">
                <label for="contraseña" class="form-label">Contraseña Nueva</label>
                <input type="password" class="form-control" id="newpassword" required="" autocomplete="nope">
                <div class="inputError">La contraseña tiene que ser de 4 a 12 dígitos.</div>
            </div>
            <div class="col-md-6 position-relative" id="grupoconfirmarContraseña">
                <label for="confirmarContraseña" class="form-label">Confirmar contraseña</label>
                <input type="password" class="form-control" id="confirmpassword" required="" autocomplete="nope">
                <div class="inputError">Las contraseñas no coinciden.</div>
            </div>
            <!-- <div class="col-md-6 position-relative" id="grupoemail">
                <label for="email" class="form-label">Email</label>
                <input type="text" class="form-control" id="email" required="" autocomplete="nope">
                <div class="inputError">El correo solo puede contener letras, numeros, puntos, guiones y guion bajo.</div>
            </div>
            <div class="col-md-6 position-relative" id="grupotelefono">
                <label for="telefono" class="form-label">Telefono</label>
                <input type="text" class="form-control" id="telefono" required="" autocomplete="nope">
                <div class="inputError">El telefono solo puede contener numeros y el maximo son 14 dígitos.</div>
            </div> -->
            <div class="col-md-6 position-relative" id="grupointentosAcceso">
                <label for="intentosAcceso" class="form-label">Intentos de Acceso</label>
                <input type="text" class="form-control" id="tries" required="" autocomplete="nope">
                <div class="inputError">No se permiten caracteres especiales o campo vacío.</div>
            </div>
            <div class="col-md-6 position-relative" id="grupoultimoIntentoAcceso">
                <label for="ultimoIntentoAcceso" class="form-label">Ultimo Intento de Acceso</label>
                <input type="text" class="form-control" id="lasttrieddate" required="" autocomplete="nope">
                <div class="inputError">No se permiten caracteres especiales o campo vacío.</div>
            </div>
            <div class="col-md-4 position-relative" id="grupoFechaInicioReporte">
                <label for="date" class="form-label">Fecha inicio Reportes</label>
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
                <button class="btn btn-danger" type="reset" onclick="redirect()">Cancelar</button>
                <button class="btn btn-primary" type="submit" onclick="editarUsuario()">Confirmar</button>
            </div>
        </form>
        
        <div id="accordion" class="accordion">

        </div>
    </div>


<?php require('estructura/footer.php') ?>
<script src="../../modulos/toastNotification/toastNotification.js" ></script>
<script src="../../modulos/accordion/accordion.js" ></script>

<script> 

    function editarUsuario(){
        let formEditAccount = document.getElementById("formulario");

        formEditAccount.addEventListener("submit", (e) => {
            e.preventDefault();
        })

        let inputs = document.querySelectorAll('#formulario input');
        let selects = document.querySelectorAll('#formulario select');
        let bodyValue= `cmd=editarUsuario`;
        
        inputs.forEach(input => {
            bodyValue += `&form_AccountsForm[${input.id}]=${input.value}`
        });

        selects.forEach(select => {
            bodyValue += `&form_AccountsForm[${select.id}]=${select.options[select.selectedIndex].value}`
        });

        bodyValue += `&mode=edit&accountId=${getParameterByName('accountId')}`

        fetch("accountsModify_cmd.php",{
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

            
             
        })
        .catch(e => {
            toastNotification({ok: false, errorMsg: "Ha ocurrido un error de conexión."});
        })



    }

    function cargarTabla(){
        let accordion = document.getElementById("accordion")

        accordion.innerHTML = "";

        crearTabla({
            tituloColumna: ["Grupos","fa-people-group"],
            btnAction: ["accionTabla('grupos','agregar')", "accionTabla('grupos','quitar')"],
            idSelect: ["dataGrpAct","dataGrpDisp"],
            optId: "groupid",
            optName: "groupname"
        });

        accordionActions();
    }


    function cargarOpciones(idSelect, optId, optName){
        let selectId = idSelect;
        let idOption = optId;
        let nameOption = optName;

        fetch("accountsModify_cmd.php",{
            method:'POST',
            headers:{
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `cmd=cargarGrupos&form_AccountsForm[idSelect]=${selectId[0]}&form_AccountsForm[accountId]=`+ getParameterByName('accountId')
        })
        .then( peticion => peticion.json() )
        .then( res=>{
            if (res.redirectPage) {
                redirect(res.redirectPage);
                 return
            }
               if (res.ok) {
                let datos = ["datosActuales","datosDisponibles"]
                
                for (let i = 0; i < 2; i++) {
                    let objetoDatos = res[datos[i]];
                    cargarOpcionesSelect(selectId[i], objetoDatos, idOption, nameOption, false);
                }
            }
            else{
                // Mensaje de error de la base de datos
            }
        })
        .catch(e => {
            toastNotification({ok: false, errorMsg: "Ha ocurrido un error de conexión."});
        })
    }

    function cargarFormulario(){
        let formulario = document.getElementById('formulario');

        formulario.addEventListener('submit', (e) => {
            e.preventDefault();
        })
        
        fetch("accountsModify_cmd.php",{
            method:'POST',
            headers:{
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `cmd=cargarFormulario&accountId=`+ getParameterByName('accountId')
        })
        .then( peticion => peticion.json() )
        .then( res=>{
            if (res.redirectPage) {
                redirect(res.redirectPage);
                 return
            }
            if (res.ok) {
                let objAccount = res.data;
                for (let i = 0; i < Object.keys(objAccount).length; i++) {
                    let id = Object.keys(objAccount)[i];
                    if( document.getElementById(id) != null && objAccount[id] != null ){
                        if(id == "fecha_desde_reporte" || id == "validez_desde" || id == "validez_hasta" ){
                            let formatDate = (objAccount[id]).substr(0,10);
                            if (!formatDate.includes(0)) {
                                document.getElementById(id).value = formatDate;
                            }
                        }
                        else{
                            document.getElementById(id).value = objAccount[id];
                        }
                    }
                }

            }
            else{
                // Mensaje de error de la base de datos
            }
        })
        .catch(e => {
            toastNotification({ok: false, errorMsg: "Ha ocurrido un error de conexión."});
        })
    }

    function cargarSelects(){

        let dataBase = ["accounts_cmd.php"];
        let selectId = ["cliente_id"];
        let idOption = ["cliente_id"];
        let nameOption = ["cliente_nombre"];

        for (let i = 0; i < dataBase.length; i++) {
            fetch(dataBase[i],{
                method:'POST',
                headers:{
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `cmd=cargarTabla&form_AccountsForm[newAccount]=false`
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

    function accionTabla(tabla, accion){

        var selectId;
        var cmd;
        var search;

        if (tabla == "grupos") {
            if(accion == "agregar" ){
                cmd = "agregarGrupos";
                selectId = "dataGrpDisp";
                search = "availablegroups";
            }
            if(accion == "quitar"){
                cmd = "quitarGrupos";
                selectId = "dataGrpAct";
                search = "accountgroups";
            }
        }
        
        let optionsSelected = getSelectValues(selectId);
        let selects = document.querySelectorAll(`.activeBody select`);
        let idSelects = [selects[0].getAttribute("id"), selects[1].getAttribute("id")];
        let optId = document.querySelector(`.activeBody #${selectId}`).getAttribute("idoption");
        let optName = document.querySelector(`.activeBody #${selectId}`).getAttribute("nameoption");
        if (optName.includes(",")) {
            optName = optName.split(",")
        }
        fetch("accountsModify_cmd.php",{
            method:'POST',
            headers:{
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `cmd=${cmd}&accountId=`+getParameterByName('accountId')+`&form_AccountsForm[${search}]=${optionsSelected}`
        })
        .then( peticion => peticion.json() )
        .then( res=>{
            if (res.redirectPage) {
                redirect(res.redirectPage);
                 return
            }
            toastNotification(res);
            cargarOpciones(idSelects, optId, optName);


        })
        .catch(e => {
            toastNotification({ok: false, errorMsg: "Ha ocurrido un error de conexión."});
        })
    }


    window.onload = ()=>{
        cargarSelects();
        cargarFormulario();
        cargarTabla();

    }
    
    function redirect() {
        let contenido = document.getElementById("contenido");
        contenido.classList.remove('animate__fadeInDown');
        contenido.classList.add('animate__fadeOutUp');

        history.go(-1);
    }
</script> 
</body>
</html>