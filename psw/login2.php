<template>
    <div class="page" data-name="login-admin">
        <link rel="stylesheet noopener" href="php/psw/login.css" />
        <div class="page-content">

            <div class="container_login animation-in" id="container_login">
                <div class="forms-container_login">

                    <div class="signin-signup">

                        <form method="post" class="sign-in-form" id="sign-in-form">
                            <div id="divlottie">
                                <lottie-player class="" id="userActivo" src="php/psw/images/user-blue.json" background="#ffffff" speed="0.50" style="width: 200px; height: 200px;" autoplay></lottie-player>
                            </div>

                            <h2 class="title">Bienvenido</h2>
                            <div class="input-field">
                                <i class="fas fa-user"></i>
                                <input type="text" placeholder="Usuario" id="username" autocomplete="nope" />
                            </div>
                            <div class="input-field">
                                <i class="fas fa-lock"></i>
                                <input type="password" placeholder="Contraseña" id="password" />
                            </div>
                            <input type="submit" class="btn solid" value="Ingresar" @click="${login}" />
                            <p id="error" class="animate__animated " style="color:red;font-weight: bold;"></p>
                            <p id="aviso" class="animate__animated " style="color:black;font-weight: normal;"></p>

                        </form>


                        <form method="post" class="sign-up-form" id="sign-up-form">
                            <h2 class="title">Cambiar Contraseña</h2>
                            <div class="input-field">
                                <i class="fas fa-user"></i>
                                <input type="text" placeholder="Usuario" id="usernameForm2" />
                            </div>
                            <div class="input-field">
                                <i class="fas fa-lock"></i>
                                <input type="password" placeholder="Contraseña Actual" id="passwordForm2" />
                            </div>
                            <div class="input-field">
                                <i class="fas fa-lock"></i>
                                <input type="password" class="contraseña" placeholder="Contraseña Nueva" id="password_newForm2" />
                            </div>
                            <div class="input-field">
                                <i class="fas fa-lock"></i>
                                <input type="password" class="confirmarContraseña" placeholder="Confirmar Contraseña" id="password_repForm2" />
                            </div>
                            <input type="submit" class="btn" value="Confirmar" @click="${newPass}" id="btnConfirmarNewPass" />
                            <p id="errorForm2" class="animate__animated " style="color:red;font-weight: bold;"></p>
                            <p id="avisoForm2" class="animate__animated " style="color:green;font-weight: bold;"></p>
                            <!-- </div> -->
                        </form>
                    </div>
                </div>

                <div class="panels-container_login">
                    <div class="panel-log left-panel" id="left-panel">
                        <div class="content">
                            <h3>¿Necesitas cambiar tu contraseña?</h3>
                            <p>
                                Haz click en el siguiente boton.
                            </p>
                            <button class="btn transparent" id="sign-up-btn">
                                Nueva contraseña
                            </button>
                        </div>
                        <img src="php/psw/images/log.svg" class="image" alt="" />
                    </div>
                    <div class="panel-log right-panel">
                        <div class="content">
                            <h3>¿Quieres iniciar sesión?</h3>
                            <p>
                                Haz click en el siguiente boton para iniciar sesión.
                            </p>
                            <button class="btn transparent" id="sign-in-btn">
                                Iniciar sesión
                            </button>
                        </div>
                        <img src="php/psw/images/register.svg" class="image" alt="" />
                    </div>
                </div>
            </div>

        </div>
    </div>
</template>

<script>
    export default (props, {
        $on
    }) => {


        $on('pageInit', () => {


            setTimeout(() => {

                const sign_in_btn = document.querySelector("#sign-in-btn");
                const sign_up_btn = document.querySelector("#sign-up-btn");
                const container_login = document.querySelector(".container_login");

                sign_up_btn.addEventListener("click", () => {
                    container_login.classList.add("sign-up-mode");
                });

                sign_in_btn.addEventListener("click", () => {
                    container_login.classList.remove("sign-up-mode");
                });


                setTimeout(removeAnimation, 2000);

                function removeAnimation() {
                    let container_login = document.getElementById("container_login");
                    container_login.classList.remove("animation-in-right")
                }


                let formLogin = document.getElementById("sign-in-form");
                let formNewPass = document.getElementById("sign-up-form");

                formLogin.addEventListener("submit", (e) => {
                    e.preventDefault();
                })

                formNewPass.addEventListener("submit", (e) => {
                    e.preventDefault();
                })

                // Obtener el elemento que contiene el Shadow DOM
                let contenedorShadow = document.querySelector('#userActivo');

                // Acceder al Shadow DOM
                let shadowRoot = contenedorShadow.shadowRoot;

                // Acceder a los elementos dentro del Shadow DOM
                shadowRoot.querySelectorAll('svg g[clip-path="url(#__lottie_element_2)"] g[mask="url(#__lottie_element_9)"] path').forEach(element => {
                    element.setAttribute("fill", mainColorTheme)
                });
            }, 1000);

        })

        function userCheck(status) {
            let divlottie = document.getElementById("divlottie")
            let userActivo = document.getElementById("userActivo");

            let lottie = document.createElement("lottie-player");
            lottie.setAttribute("id", "userActivo");
            lottie.setAttribute("background", "transparent");
            lottie.setAttribute("speed", "0.50");
            lottie.setAttribute("style", "width: 200px; height: 200px;");
            lottie.setAttribute("autoplay", "");

            if (status == "loginOk") {
                lottie.setAttribute("src", "php/psw/images/user-green.json");
            } else {
                lottie.setAttribute("src", "php/psw/images/user-red.json");
            }


            userActivo.classList.add("animate__animated");
            userActivo.classList.add("animate__fadeOut");
            setTimeout(() => {
                userActivo.classList.add("hidden");
                divlottie.innerHTML = "";
                divlottie.appendChild(lottie);
            }, 500)


        }

        const login = () => {

            let username = document.getElementById("username").value;
            let password = document.getElementById("password").value;

            fetch("php/psw/login_cmd.php", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: `cmd=login&form_login[username]=${username}&form_login[password]=${password}`
                })
                .then(a => a.json()).then(async (dato) => {

                    if (dato.ok) {

                        userCheck("loginOk");

                        await setConf()
                        await getTokenCSRF()

                        setTimeout(() => {

                            document.getElementById("container_login").classList.remove("animation-in");
                            document.getElementById("container_login").classList.add("animate__animated");
                            document.getElementById("container_login").classList.add("animate__fadeOut");

                            startAccionesTipoUsuario()

                            redirect({
                                page: "home-admin"
                            })

                            // redirect({
                            //     page: "welcome"
                            // })
                        }, 1500)
                    } else {
                        userCheck();
                        let error = document.getElementById("error");
                        error.innerHTML = dato.errorMsg;
                        error.classList.add("animate__bounceIn");
                        document.getElementById("aviso").innerHTML = dato.avisoMsg;
                    }

                })
        }


        const newPass = () => {
            let username = document.getElementById("usernameForm2").value;
            let password = document.getElementById("passwordForm2").value;
            let password_new = document.getElementById("password_newForm2").value;
            let password_rep = document.getElementById("password_repForm2").value;


            fetch("php/psw/login_cmd.php", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: `cmd=newPass&form_login[username]=${username}&form_login[password]=${password}&form_login[password_new]=${password_new}&form_login[password_rep]=${password_rep}`
                })
                .then(a => a.json())
                .then(dato => {
                    let aviso = document.getElementById("avisoForm2");
                    let error = document.getElementById("errorForm2");

                    if (dato.ok) {
                        error.innerHTML = "";
                        aviso.innerHTML = "Contraseña cambiada con éxito.";
                        aviso.classList.add("animate__bounceIn");

                        setTimeout(() => {
                            const container_login = document.querySelector(".container_login");
                            container_login.classList.remove("sign-up-mode");
                        }, 1500);


                    } else {
                        aviso.innerHTML = "";
                        error.innerHTML = dato.errorMsg;
                        error.classList.add("animate__bounceIn");
                    }


                })
        }





        return $render;
    }
</script>