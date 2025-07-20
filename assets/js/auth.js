// === Tema oscuro ===
function applyDarkMode(isDark) {
  document.body.classList.toggle("dark-mode", isDark);
  const toggle = document.getElementById("themeSwitcher");
  if (toggle) toggle.checked = isDark;
  localStorage.setItem("darkMode", isDark);
}

function toggleDarkMode() {
  const isDark = !document.body.classList.contains("dark-mode");
  applyDarkMode(isDark);
}

// === Traducciones dinámicas ===
const translations = {
  es: {
    title: "Bienvenido",
    subtitle: "Regístrate para acceder a tu dashboard de AutoPublicador",
    register: "Registrarse",
    login: "Iniciar sesión",
    haveAccount: "¿Ya tienes una cuenta?",
    createAccount: "¿Nuevo aquí?",
    country: "País",
    firstName: "Nombre",
    lastName: "Apellido",
    province: "Provincia / Estado",
    city: "Ciudad",
    email: "Correo electrónico",
    password: "Contraseña",
    footer: "Bienvenido a la herramienta automática de publicación.",
    powered: "Powered by",
    remember: "Recuérdame",
    forgotPassword: "¿Olvidaste tu contraseña?"
  },
  en: {
    title: "Welcome",
    subtitle: "Sign up to access your AutoPublisher dashboard",
    register: "Sign Up",
    login: "Sign In",
    haveAccount: "Already have an account?",
    createAccount: "New here?",
    country: "Country",
    firstName: "First Name",
    lastName: "Last Name",
    province: "State / Province",
    city: "City",
    email: "Email",
    password: "Password",
    footer: "Welcome to the automatic publishing tool.",
    powered: "Powered by",
    remember: "Remember me",
    forgotPassword: "Forgot password?"
  }
};


function applyLanguage(lang) {
  const t = translations[lang] || translations.es;
  document.documentElement.lang = lang;
  localStorage.setItem("lang", lang);

  document.querySelectorAll("[data-i18n]").forEach(el => {
    const key = el.getAttribute("data-i18n");
    if (t[key]) el.textContent = t[key];
  });

  const placeholders = {
    first_name: t.firstName,
    last_name: t.lastName,
    province: t.province,
    city: t.city,
    email: t.email,
    password: t.password
  };

  for (const [name, value] of Object.entries(placeholders)) {
    const input = document.querySelector(`[name='${name}']`);
    if (input) input.placeholder = value;
  }
}

function toggleLanguage() {
  const current = localStorage.getItem("lang") || "es";
  const newLang = current === "es" ? "en" : "es";
  applyLanguage(newLang);
}


function loadCountries(selectId = "countrySelect") {
  const select = document.getElementById("country") || document.getElementById(selectId);
  if (!select) return;

  fetch("https://restcountries.com/v3.1/all")
    .then(res => {
      if (!res.ok) throw new Error("Respuesta inválida");
      return res.json();
    })
    .then(data => {
      if (!Array.isArray(data)) throw new Error("Formato incorrecto");
      const countries = data.map(c => c.name.common).sort();
      populateCountrySelect(select, countries);
    })
    .catch((err) => {
      console.warn("Fallo al cargar desde API. Usando datos locales.", err);

      // Lista mínima pero funcional de países
      const fallbackCountries = [
        "Argentina", "México", "Chile", "España", "Estados Unidos",
        "Colombia", "Perú", "Uruguay", "Paraguay", "Venezuela",
        "Brasil", "Canadá", "Reino Unido", "Francia", "Alemania",
        "Italia", "Japón", "China", "India", "Australia"
      ];
      populateCountrySelect(select, fallbackCountries);

      Swal.fire({
        icon: "info",
        title: "Conexión limitada",
        text: "Mostrando una lista limitada de países por falta de conexión.",
        confirmButtonColor: "#2196F3"
      });
    });
}

function populateCountrySelect(select, countries) {
  countries.forEach(name => {
    const option = document.createElement("option");
    option.value = name;
    option.textContent = name;
    select.appendChild(option);
  });
}


// === SweetAlert2 mensajes ===
function showSuccess(msg) {
  Swal.fire({
    icon: "success",
    title: "✔️",
    text: msg,
    confirmButtonColor: "#2196F3"
  });
}

function showError(msg) {
  Swal.fire({
    icon: "error",
    title: "❌",
    text: msg,
    confirmButtonColor: "#d32f2f"
  });
}

// Mostrar errores según parámetros URL
const params = new URLSearchParams(window.location.search);
if (params.has("error")) {
  const errType = params.get("error");

  const messages = {
    campos: "Por favor, completa todos los campos.",
    invalid: "Correo o contraseña incorrectos.",
    db: "Error de conexión con la base de datos.",
    method: "Acceso no permitido."
  };

  showError(messages[errType] || "Ha ocurrido un error.");
}


// === Inicio ===
window.addEventListener("DOMContentLoaded", () => {
  const storedDark = localStorage.getItem("darkMode") === "true";
  applyDarkMode(storedDark);

  const lang = localStorage.getItem("lang") || "es";
  applyLanguage(lang);

  loadCountries(); // ✅ Aquí se ejecuta correctamente

  const themeToggle = document.getElementById("themeSwitcher");
  if (themeToggle) {
    themeToggle.checked = storedDark;
    themeToggle.addEventListener("change", toggleDarkMode);
  }

  const langBtn = document.getElementById("langToggle");
  if (langBtn) {
    langBtn.addEventListener("click", toggleLanguage);
  }

  const params = new URLSearchParams(window.location.search);
  if (params.has("success")) showSuccess("Registro exitoso. ¡Bienvenido!");
  if (params.has("error")) showError("Hubo un error. Intenta nuevamente.");
});
