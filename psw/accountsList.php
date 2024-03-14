<?php require('estructura/header.php') ?>
<link rel="stylesheet noopener" href="../../modulos/toastNotification/toastNotification.css">
<link rel="stylesheet noopener" href="../../modulos/popup/popup.css">
<link rel="stylesheet noopener" href="../../modulos/searchBar/searchBar.css">
<link rel="stylesheet noopener" href="../../modulos/floatingButton/floatingButton.css">
<link rel="stylesheet noopener" href="../../modulos/nextPrevArrow/nextPrevArrow.css">

<title>Usuarios</title>
</head>
<style>
    .divHeader {
        display: flex;
    }

    nav li {
    cursor: pointer;
    }

    th.sortTh {
        display: flex;
    }

    .actionSB {
        background: #252525;
        width: 18px;
        height: 18px;
        border-radius: 50px;
        display: flex;
        justify-content: center;
        align-items: center;
        cursor: pointer;
        margin-left: 10px;
        margin-top: 2px;
        transition: all 0.6s cubic-bezier(0.68, -0.55, 0.265, 1.55);
    }

    body.dark .actionSB {
        background: #f1eded;
    }

    .actionSB span {
        color: #f1eded;
    }

    body.dark .actionSB span {
        color: #252525;
    }

    .actionSB.DESC{
        transform: rotate(180deg);
    }

    td.buttonHeight {
        width: 0px;
    }

    th.numberHeight {
        width: 0px;
        padding-left: 20px;
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

    <div  class="container animate__animated animate__fadeInDown animate__faster " id="contenido">
        <div class="divHeader container">
            <div class="header-section animate__animated" id="headerTitle">
                <h2 class="title">Lista de <span>Usuarios</span></h2>
            </div>
            <div class="input-searchBar hidden animate__animated" id="searchBar">
                <input type="text" placeholder="Buscar..." id="search" onkeyup="cargarTabla()" autocomplete="nope">
                <span class="search">
                    <i class="fa-solid fa-magnifying-glass search-icon"></i>
                </span>
                <span class="closeSearchBar" onclick="closeSearchBar()">
                    <i class="fa-solid fa-xmark close-icon" id="close-icon"></i>
                </span>
            </div>
            
            <div class="circCont">
                <button class="circle boxShadow" data-animation="fadeOut" data-remove="3000"
                    onclick="redirect()"></button>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-hover table-striped">

            <div class="nextPrevArrow">
                <ul>
                    <li class="prev" onclick="prevPage()"><span></span></li>
                    <li class="next" onclick="nextPage()"><span></span></li>
                </ul>
            </div>

                <thead class="table-dark">
                    <tr>
                        <th scope="col" ></th>
                        <th scope="col" ></th>
                        <th scope="col" class="numberHeight">#</th>
                        <th scope="col" class="sortTh">Nombre
                            <div class="actionSB" id="filterName" onclick="actionSort()">
                                <span class=""><i class="fa-solid fa-angle-up"></i></span>
                            </div>
                        </th>
                        <th scope="col">Usuario</th>
                        <th scope="col">Grupo</th>
                    </tr>
                </thead>
                <tbody id="bodyTable" >
                </tbody>
            </table>

        </div>
    </div>

    <div class="overlay" id="overlay">
        <div class="popup" id="popup">
            <a href="#" id="btn-cerrar-popup" class="btn-cerrar-popup"><i class="fas fa-times"></i></a>
            <h4 id="tituloPopUp"></h4>
            <form id="formDeleteAccount">
                <button class="btn btn-sm btn-primary" id="btnAceptarEliminar">Aceptar</button>
                <button class="btn btn-sm btn-danger" id="btnCancelar">Cancelar</button>
            </form>
        </div>
    </div>

    
    <div class="actionFB animate__animated animate__faster" id="floatingButton" onclick="actionToggle()">
        <span class="iconToggle">
            <i class="fa-solid fa-ellipsis"></i>
        </span>
        <ul>
            <li onclick="redirect('accounts.php')"><i class="fa-solid fa-plus icono"></i> Nuevo Usuario</li>
            <li onclick="openSearchBar()"><i class="fa-solid fa-magnifying-glass icono"></i>Buscar</li>
        </ul>
    </div>




<?php require('estructura/footer.php') ?>
<script src="../../modulos/toastNotification/toastNotification.js" ></script>
<script src="../../modulos/searchBar/searchBar.js" ></script>
<script src="../../modulos/floatingButton/floatingButton.js" ></script>
<script src="../../modulos/sortButton/sortButton.js" ></script>

<script>
    var btnAbrirPopup = document.getElementById('btn-abrir-popup'),
	overlay = document.getElementById('overlay'),
	popup = document.getElementById('popup'),
	btnCerrarPopup = document.getElementById('btn-cerrar-popup'),
	btnCancelar = document.getElementById('btnCancelar'),
	btnAceptarEliminar = document.getElementById('btnAceptarEliminar'),
	tituloPopUp = document.getElementById('tituloPopUp'),
	formDeleteAccount = document.getElementById('formDeleteAccount');
    var accountid;

    function openPopupEliminar(username, idcuenta){
        accountid = idcuenta;
        tituloPopUp.innerHTML = "¿Seguro que desea eliminar el usuario "+ username +" ?";
        formDeleteAccount.removeAttribute("style");
        overlay.classList.add('active'); 
        popup.classList.add('active');
    }

    btnAceptarEliminar.addEventListener('click', ()=>{
        eliminarUsuario(accountid);
        overlay.classList.remove('active');
        popup.classList.remove('active');
    });

    btnCerrarPopup.addEventListener('click', function(e){
        e.preventDefault();
        overlay.classList.remove('active');
        popup.classList.remove('active');
    });

    btnCancelar.addEventListener('click', function(e){
        e.preventDefault();
        overlay.classList.remove('active');
        popup.classList.remove('active');
    });
</script>

<script>
    var sort = "asc";
    var offset = 0;
    var limit = 12;

    function nextPage(){
        offset = offset + limit;
        cargarTabla(sort,offset,limit);
    }

    function prevPage(){
        if(offset != 0){
            offset = offset - limit;
        }
        cargarTabla(sort,offset,limit);
    }

    function cargarTabla(sort = "ASC", offsetDB = 0, limitDB = 0){

        let search = document.getElementById("search").value;

        if(search == ""){
            offsetDB = offset;
            limitDB = limit;
        }
            
        fetch("accountsList_cmd.php",{
            method:'POST',
            headers:{
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `cmd=cargarTabla&search=${search}&sort=${sort}&offset=${offsetDB}&limit=${limitDB}`

        })
        .then( peticion => peticion.json() )
        .then( res=>{
            if (res.redirectPage) {
                redirect(res.redirectPage);
                return
            }
            if (res.ok) {
                let objAccounts = res.data;
                if( objAccounts.length != 0){

                    let bodyTable = document.getElementById("bodyTable");
                    bodyTable.innerHTML = "";
                    
                    var docFragment = document.createDocumentFragment();
                    
                    for (let i = 0; i < objAccounts.length; i++) {
                        let tr = document.createElement("tr");
                        tr.classList.add("uppercase");

                        
                        let a = ["btn-info","btn-danger"];
                        let b = ["btn-outline-info","btn-outline-danger"];
                        let c = ["Editar", "Eliminar"];
                        let d = ["edit","delete"];
                        let e = ["fa-pencil","fa-trash"];
    
                        for (let j = 0; j < 2; j++) {
                            let tdBtn = document.createElement("td");
                            tdBtn.classList.add("buttonHeight");

                            if (objAccounts[i][d[j]]) {
                                let btn = document.createElement("button");
                                btn.classList.add("btn");
                                btn.classList.add("btn-sm");
                                btn.classList.add(a[j]);
                                btn.setAttribute("title",c[j]);
                                if (j == 0) {
                                    btn.setAttribute("onClick",`redirect('accountsModify.php?accountId=${objAccounts[i]["accountid"]}')` );
                                }
                                else{
                                    btn.setAttribute("onClick",`openPopupEliminar("${objAccounts[i]['username']}","${objAccounts[i]['accountid']}")` );
                                }

                                let icono = document.createElement("i");
                                icono.classList.add("fa-solid");
                                icono.classList.add(e[j]);
        
                                btn.appendChild(icono);
                                tdBtn.appendChild(btn);
                            }
                            
                            tr.appendChild(tdBtn);
    
                        }

                                                
                        let thOrden = document.createElement("th");
                        thOrden.innerHTML= offset + i+1;
                        thOrden.classList.add("numberHeight")
                        tr.appendChild(thOrden);

                        let tdAccount = document.createElement("td");
                        tdAccount.innerHTML= objAccounts[i]["lastname"] + ", " + objAccounts[i]["firstname"] ;
    
                        let tdUsuario = document.createElement("td");
                        tdUsuario.innerHTML= objAccounts[i]["username"];
    
                        let tdNombreGrupo = document.createElement("td");
                        tdNombreGrupo.innerHTML= objAccounts[i]["grupos"];

                        tr.appendChild(tdAccount);
                        tr.appendChild(tdUsuario);
                        tr.appendChild(tdNombreGrupo);

                        docFragment.appendChild(tr)
                    }
                    bodyTable.appendChild(docFragment);
                }else{
                    prevPage();
                }
     
                
            }
        })
        .catch(e => {
            toastNotification({ok: false, errorMsg: "Ha ocurrido un error de conexión."});
        })
    }

    function eliminarUsuario(accountid){
        let formDeleteAccount = document.getElementById("formDeleteAccount");

        formDeleteAccount.addEventListener("submit", (e) => {
            e.preventDefault();
        })

        fetch("accountsList_cmd.php",{
            method:'POST',
            headers:{
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body:  `cmd=eliminarUsuario&form_GroupsForm[accountid]=${accountid}`
        })
        .then( peticion => peticion.json() )
        .then( res=>{
            if (res.redirectPage) {
                redirect(res.redirectPage);
                return
            }
            toastNotification(res);
            limpiarFormulario();
            cargarTabla(sort,offset,limit);
             
        })
        .catch(e => {
            toastNotification({ok: false, errorMsg: "Ha ocurrido un error de conexión."});
        })

    }

    window.onload = ()=>{
        cargarTabla(sort,offset,limit);
        
    }

    function redirect(pagina = 'usuarioServices.html') {
        let contenido = document.getElementById("contenido");
        contenido.classList.replace('animate__fadeInDown','animate__fadeOutUp');

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