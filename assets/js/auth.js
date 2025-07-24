// === Modo Oscuro ===
function applyDarkMode(isDark) {
  document.body.classList.toggle("dark-mode", isDark);
  const toggle = document.getElementById("themeSwitcher");
  if (toggle) toggle.checked = isDark;
  localStorage.setItem("darkMode", isDark);
}

function toggleDarkMode() {
  applyDarkMode(!document.body.classList.contains("dark-mode"));
}

// === Traducción Dinámica ===
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
    powered: "Desarrollado por",
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
  localStorage.setItem("lang", lang);
  document.documentElement.lang = lang;

  // Textos
  document.querySelectorAll("[data-i18n]").forEach(el => {
    const key = el.getAttribute("data-i18n");
    if (t[key]) el.textContent = t[key];
  });

  // Placeholders
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
  applyLanguage(current === "es" ? "en" : "es");
}

// === Países dinámicos ===
function loadCountries(selectId = "country") {
  const select = document.getElementById(selectId);
  if (!select) return;

  fetch("https://restcountries.com/v3.1/all")
    .then(res => res.ok ? res.json() : Promise.reject())
    .then(data => {
      const countries = data.map(c => c.name.common).sort();
      populateCountrySelect(select, countries);
    })
    .catch(() => {
      console.warn("Fallo API. Usando lista local.");
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
        text: "Lista de países limitada por falta de conexión.",
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

// === Mensajes SweetAlert2 ===
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

// === Mostrar errores de URL ===
function checkUrlErrors() {
  const params = new URLSearchParams(window.location.search);
  if (params.has("error")) {
    const errType = params.get("error");
    const messages = {
      campos: "Por favor, completa todos los campos.",
      invalid: "Correo o contraseña incorrectos.",
      db: "Error de conexión con la base de datos.",
      method: "Acceso no permitido.",
      passwords_no_match: "Las contraseñas no coinciden.",
      exists: "El correo o usuario ya está registrado.",
      captcha: "Captcha inválido. Intenta nuevamente."
    };
    showError(messages[errType] || "Ha ocurrido un error.");
  }

  if (params.has("success")) {
    showSuccess("Registro exitoso. ¡Bienvenido!");
  }
}

// === Init ===
window.addEventListener("DOMContentLoaded", () => {
  const storedDark = localStorage.getItem("darkMode") === "true";
  applyDarkMode(storedDark);

  const lang = localStorage.getItem("lang") || "es";
  applyLanguage(lang);

  loadCountries();
  checkUrlErrors();

  const themeToggle = document.getElementById("themeSwitcher");
  if (themeToggle) {
    themeToggle.checked = storedDark;
    themeToggle.addEventListener("change", toggleDarkMode);
  }

  const langBtn = document.getElementById("langToggle");
  if (langBtn) {
    langBtn.addEventListener("click", toggleLanguage);
  }
});
