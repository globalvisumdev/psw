<?php require('estructura/header.php') ?>

<link rel="stylesheet noopener" href="../../modulos/toastNotification/toastNotification.css">
<link rel="stylesheet noopener" href="../../modulos/accordion/accordion.css">
<script src="https://unpkg.com/@lottiefiles/lottie-player@latest/dist/lottie-player.js"></script>


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
                <h2 class="title">Grupo de Usuarios y <span>Permisos</span></h2>
            </div>
            <div class="circCont">
                <button class="circle boxShadow" data-animation="fadeOut" data-remove="3000"
                    onclick="redirect()"></button>
            </div>
        </div>

        <form class="row g-2  animate__animated animate__fadeIn" id="formulario">
            <div class="col-md-6 position-relative" id="grupoUsuario">
                <label for="Grupo" class="form-label">Grupo</label>
                <select class="form-select uppercase" id="groupSelect" required="" onchange="cargarTabla()">
                    <option selected="" disabled=""> Seleccione un Grupo</option>
                </select>
            </div>
        </form>

        
        <h3 class="titulo uppercase" id="titulo" ></h2>
        <div id="accordion" class="accordion">

        </div>
    </div>

    <div class="divLottie hidden" id="loading">
        <lottie-player src="./images/loading.json" background="transparent"  speed="0.50"  style="width: 150px; height: 150px;" autoplay loop></lottie-player>            
    </div>


</body>

<?php require('estructura/footer.php') ?>
<script src="../../modulos/toastNotification/toastNotification.js" ></script>
<script src="../../modulos/accordion/accordion.js" ></script>

<script>
    
    function cargarTitulo(){
        let groupSelect = document.getElementById("groupSelect");
        let titulo = document.getElementById("titulo");
        let selectText = groupSelect.options[groupSelect.selectedIndex].text;
    
        titulo.classList.add("hidden");

        if((titulo.classList).contains("animate__fadeInDown")){
            titulo.classList.remove("animate__animated")
            titulo.classList.remove("animate__fadeInDown")
        }
        setTimeout( datoTitulo, 250);
    
        function datoTitulo(){
            titulo.classList.remove("hidden");
            titulo.classList.add("animate__animated")
            titulo.classList.add("animate__fadeInDown")
            titulo.innerHTML = "Información sobre el Grupo " + selectText;
        } 
    }

    function cargarTabla(){

        // let loading = document.getElementById("loading");
        // loading.classList.remove("hidden")
            
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
            tituloColumna: [ "Usuarios", "fa-users"],
            btnAction: ["accionTabla('usuarios', 'agregar')", "accionTabla('usuarios', 'quitar')"],
            idSelect: ["dataUsrAct", "dataUsrDisp"],
            optId: "accountid",
            optName: ["lastname","firstname"]
        });
        crearTabla({
            tituloColumna: ["Empresas", "fa-building"],
            btnAction: ["accionTabla('empresas', 'agregar')", "accionTabla('empresas', 'quitar')"],
            idSelect: ["dataEmprAct", "dataEmprDisp"],
            optId: "empresa_id",
            optName: "empresa_nombre"
        });

        accordionActions();
        
        
    }

    function cargarOpciones(idSelect, optId, optName){
        let groupSelect = document.getElementById("groupSelect");
        let groupid = groupSelect.options[groupSelect.selectedIndex].value; 
        
        let selectId = idSelect;
        let idOption = optId;
        let nameOption = optName;
        
        fetch("groupAccountAndPermission_cmd.php",{
            method:'POST',
            headers:{
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `cmd=cargarOpciones&form_GroupsForm[groupid]=${groupid}&form_GroupsForm[idSelect]=${selectId[0]}`
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

        let selectId = "groupSelect";
        let idOption = "groupid";
        let nameOption = "groupname";
        
        fetch("groups_cmd.php",{
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
        let groupSelect = document.getElementById("groupSelect");
        let groupid = groupSelect.options[groupSelect.selectedIndex].value; 
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
                search = "groupactions";
            }
        }
        if (tabla == "usuarios") {
            if(accion == "agregar" ){
                cmd = "agregarUsuarios";
                selectId = "dataUsrDisp";
                search = "allAccountsExceptGroup";
            }
            if(accion == "quitar"){
                cmd = "quitarUsuarios";
                selectId = "dataUsrAct";
                search = "groupaccounts";
            }
        }
        if (tabla == "empresas") {
            if(accion == "agregar" ){
                cmd = "agregarEmpresas";
                selectId = "dataEmprDisp";
                search = "allEmpresasExceptGroup";
            }
            if(accion == "quitar"){
                cmd = "quitarEmpresas";
                selectId = "dataEmprAct";
                search = "groupempresa";
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

        fetch("groupAccountAndPermission_cmd.php",{
            method:'POST',
            headers:{
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `cmd=${cmd}&form_GroupsForm[groupid]=${groupid}&form_GroupsForm[${search}]=${optionsSelected}`
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
    }
    
    function redirect(pagina = 'usuarioServices.html') {
        let contenido = document.getElementById("contenido");
        contenido.classList.remove('animate__fadeInDown');
        contenido.classList.add('animate__fadeOutUp');

               setTimeout(() => {
             setTimeout(() => {
                contenido.classList.replace('animate__fadeOut','animate__fadeIn');
            }, 500);
            window.location = pagina;
        }, 600);
    }
</script>
</html>