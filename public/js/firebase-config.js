// public/js/firebase-config.js
import { initializeApp } from "https://www.gstatic.com/firebasejs/10.12.0/firebase-app.js";
import { getAuth } from "https://www.gstatic.com/firebasejs/10.12.0/firebase-auth.js";

const firebaseConfig = {
  apiKey: "AIzaSyARfM5_QDtjHvpBcn55OcfskO9BSbrFILE",
  authDomain: "its2025.firebaseapp.com",
  projectId: "its2025",
  storageBucket: "its2025.firebasestorage.app",
  messagingSenderId: "471440425690",
  appId: "1:471440425690:web:d975b9cf4bfb5363e5dbe3",
  measurementId: "G-VDK8ESXS23"
};

// Initialisation Firebase
const app = initializeApp(firebaseConfig);
const auth = getAuth(app);

export { auth }; // ⚡ On exporte auth pour pouvoir l’importer ailleurs
