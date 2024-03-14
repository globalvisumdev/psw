<?php require('estructura/header.php') ?>
<link rel="stylesheet noopener" href="../../modulos/toastNotification/toastNotification.css">
<link rel="stylesheet noopener" href="../../modulos/accordion/accordion.css">


 <title>Usuarios y Permisos</title>
</head>
<style>
    .titulo{
        text-align: center;
        margin-top: 20px;
    }

    body.dark .titulo{
        color: #f1eded;
    }

    tr{
        text-align: center;
    } 

    th.columnTitle{
        width: 48%;
    }
    .divHeader {
        display: flex;
    }

    

</style>

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
                <h2 class="title">Usuarios y <span>Permisos</span></h2>
            </div>
            <div class="circCont">
                <button class="circle boxShadow" data-animation="fadeOut" data-remove="3000"
                    onclick="redirect()"></button>
            </div>
        </div>

        <form class="row g-2  animate__animated animate__fadeIn" id="formulario">
            <div class="col-md-6 position-relative" id="grupoUsuario">
                <label for="Usuario" class="form-label">Usuario</label>
                <select class="form-select uppercase" id="usuarioSelect" required="" onchange="cargarTabla()">
                    <option selected="" disabled=""> Seleccione un Usuario</option>
                </select>
            </div>
        </form>

        <h3 class="titulo uppercase" id="titulo" ></h2>
        <div id="accordion" class="accordion">

        </div>
    </div>


<?php require('estructura/footer.php') ?>

<script src="../../modulos/toastNotification/toastNotification.js" ></script>
<script src="../../modulos/accordion/accordion.js" ></script>

<script>
    
    function cargarTitulo(){
        let usuarioSelect = document.getElementById("usuarioSelect");
        let titulo = document.getElementById("titulo");
        let selectText = usuarioSelect.options[usuarioSelect.selectedIndex].text;
    
        titulo.classList.add("hidden");

        if((titulo.classList).contains("animate__fadeInDown")){
            titulo.classList.remove("animate__animated")
            titulo.classList.remove("animate__fadeInDown")
        }
        setTimeout( datosTitulo, 250);
    
        function datosTitulo(){
            titulo.classList.remove("hidden");
            titulo.classList.add("animate__animated")
            titulo.classList.add("animate__fadeInDown")
            titulo.innerHTML = "Información sobre el Usuario " + selectText;
        } 
    }

    function cargarTabla(){
        let accordion = document.getElementById("accordion");
        
        accordion.classList.add("hidden");
        if((accordion.classList).contains("animate__fadeInUp")){
            accordion.classList.remove("animate__animated")
            accordion.classList.remove("animate__fadeInUp")
        }
        setTimeout( animationTables, 250);
        
        function animationTables(){
            accordion.classList.remove("hidden");
            accordion.classList.add("animate__animated")
            accordion.classList.add("animate__fadeInUp")
        } 

        accordion.innerHTML = "";

        cargarTitulo();
        crearTabla({
            tituloColumna: ["Acciones","fa-screwdriver-wrench"],
            btnAction: ["accionTabla('acciones','agregar')", "accionTabla('acciones','quitar')"],
            idSelect: ["dataAccAct","dataAccDisp"],
            optId: "actionid",
            optName: "actionname"
        });
        crearTabla({
            tituloColumna: [ "Clientes", "fa-users"],
            btnAction: ["accionTabla('clientes', 'agregar')", "accionTabla('clientes', 'quitar')"],
            idSelect: ["dataCliAct", "dataCliDisp"],
            optId: "clienteid",
            optName: "cliente_nombre"
        });

        crearTabla({
            tituloColumna: [ "Empresas", "fa-building"],
            btnAction: ["accionTabla('empresas', 'agregar')", "accionTabla('empresas', 'quitar')"],
            idSelect: ["dataEmprAct", "dataEmprDisp"],
            optId: "empresa_id",
            optName: "empresa_nombre"
        });
        crearTabla({
            tituloColumna: [ "Jurisdicciones", "fa-gavel"],
            btnAction: ["accionTabla('jurisdicciones', 'agregar')", "accionTabla('jurisdicciones', 'quitar')"],
            idSelect: ["dataJurisAct", "dataJurisDisp"],
            optId: "jurisdiccionid",
            optName: "jur_nombre"
        });
        crearTabla({
            tituloColumna: [ "Vehiculos", "fa-car"],
            btnAction: ["accionTabla('vehiculos', 'agregar')", "accionTabla('vehiculos', 'quitar')"],
            idSelect: ["dataVehiAct", "dataVehiDisp"],
            optId: "vehiculo_id",
            optName: "vehiculo_nombre"
        });
        crearTabla({
            tituloColumna: [ "Servicios", "fa-note-sticky"],
            btnAction: ["accionTabla('servicios', 'agregar')", "accionTabla('servicios', 'quitar')"],
            idSelect: ["dataServAct", "dataServDisp"],
            optId: "horc_id",
            optName: "horcnombre"
        });
        crearTabla({
            tituloColumna: [ "Clientes Equivalentes", "fa-note-sticky"],
            idSelect: ["dataCliEquiAct", "dataCliEquiDisp"],
            optId: "accountid",
            optName: "nombre",
            btnAction: ["accionTabla('clientesEquivalentes', 'agregar')", "accionTabla('clientesEquivalentes', 'quitar')"],
        });

        accordionActions();

    }

    function cargarOpciones(idSelect, optId, optName){
        let usuarioSelect = document.getElementById("usuarioSelect");
        let accountid = usuarioSelect.options[usuarioSelect.selectedIndex].value; 

        let selectId = idSelect;
        let idOption = optId;
        let nameOption = optName;
        
        fetch("accountAndPermission_cmd.php",{
            method:'POST',
            headers:{
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `cmd=cargarOpciones&form_GroupsForm[accountid]=${accountid}&form_GroupsForm[idSelect]=${selectId[0]}`
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

    function cargarSelects(){

        let selectId = "usuarioSelect";
        let idOption = "accountid";
        let nameOption = ["lastname", "firstname"];
        
        fetch("accountsList_cmd.php",{
            method:'POST',
            headers:{
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `cmd=cargarTabla`
        })
        .then( peticion => peticion.json() )
        .then( res=>{
            if (res.redirectPage) {
                redirect(res.redirectPage);
                 return
            }
            if (res.ok) {
                let objetoDatos = res.data;

                cargarOpcionesSelect(selectId, objetoDatos, idOption, nameOption);
                
            }
            else{
                // Mensaje de error de la base de datos
            }

             
        })
        .catch(e => {
            toastNotification({ok: false, errorMsg: "Ha ocurrido un error de conexión."});
        })
    }


    function accionTabla(tabla, accion){
        let usuarioSelect = document.getElementById("usuarioSelect");
        let accountid = usuarioSelect.options[usuarioSelect.selectedIndex].value; 
        var selectId;
        var cmd;
        var search;

        if (tabla == "acciones") {
            if(accion == "agregar" ){
                cmd = "agregarAcciones";
                selectId = "dataAccDisp";
                search = "allactions";
            }
            if(accion == "quitar"){
                cmd = "quitarAcciones";
                selectId = "dataAccAct";
                search = "accountactions";
            }
        }
        if (tabla == "clientes") {
            if(accion == "agregar" ){
                cmd = "agregarClientes";
                selectId = "dataCliDisp";
                search = "allclientesclientes";
            }
            if(accion == "quitar"){
                cmd = "quitarClientes";
                selectId = "dataCliAct";
                search = "accountclientecliente";
            }
        }
        if (tabla == "empresas") {
            if(accion == "agregar" ){
                cmd = "agregarEmpresas";
                selectId = "dataEmprDisp";
                search = "allempresas";
            }
            if(accion == "quitar"){
                cmd = "quitarEmpresas";
                selectId = "dataEmprAct";
                search = "accountempresa";
            }
        }
        if (tabla == "jurisdicciones") {
            if(accion == "agregar" ){
                cmd = "agregarJurisdicciones";
                selectId = "dataJurisDisp";
                search = "alljurisdicciones";
            }
            if(accion == "quitar"){
                cmd = "quitarJurisdicciones";
                selectId = "dataJurisAct";
                search = "accountjurisdiccion";
            }
        }
        if (tabla == "vehiculos") {
            if(accion == "agregar" ){
                cmd = "agregarVehiculos";
                selectId = "dataVehiDisp";
                search = "allvehiculos";
            }
            if(accion == "quitar"){
                cmd = "quitarVehiculos";
                selectId = "dataVehiAct";
                search = "accountvehiculo";
            }
        }
        if (tabla == "servicios") {
            if(accion == "agregar" ){
                cmd = "agregarServicios";
                selectId = "dataServDisp";
                search = "allservicios";
            }
            if(accion == "quitar"){
                cmd = "quitarServicios";
                selectId = "dataServAct";
                search = "accountservicio";
            }
        }
        if (tabla == "clientesEquivalentes") {
            if(accion == "agregar" ){
                cmd = "agregarClientesEquivalentes";
                selectId = "dataCliEquiDisp";
                search = "allclientesequivales";
            }
            if(accion == "quitar"){
                cmd = "quitarClientesEquivalentes";
                selectId = "dataCliEquiAct";
                search = "accountclienteequivale";
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
        
        fetch("accountAndPermission_cmd.php",{
            method:'POST',
            headers:{
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `cmd=${cmd}&form_GroupsForm[accountid]=${accountid}&form_GroupsForm[${search}]=${optionsSelected}`
        })
        .then( peticion => peticion.json() )
        .then( res=>{

            if (res.redirectPage) {
                redirect(res.redirectPage);
                 return
            }
            toastNotification(res);
            // cargarTabla();
            cargarOpciones(idSelects, optId, optName);
             
        })
        .catch(e => {
            toastNotification({ok: false, errorMsg: "Ha ocurrido un error de conexión."});
        })
    }


    window.onload = ()=>{
        cargarSelects();
    }
    
    function redirect(pagina = 'usuarioServices.html') {
        let contenido = document.getElementById("contenido");
        contenido.classList.remove('animate__fadeInDown');
        contenido.classList.add('animate__fadeOutUp');

        setTimeout(() => {
            setTimeout(() => {
                contenido.classList.replace('animate__fadeOutUp','animate__fadeInDown');
            }, 500);
            window.location = pagina;
        }, 600);
    }
    
</script>
</body>
</html>