<style>
   #loader {
  border: 16px solid #3e57e7; /* Light grey */
  border-top: 16px solid #2093e0; /* Blue */
  border-radius: 50%;
  width: 120px;
  height: 120px;
  animation: spin 2s linear infinite;
  /* ceneter loader on page */
    position: fixed;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    z-index: 1000;
}

@keyframes spin {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}
#spinner {
    position: fixed;
    width: 100%;
    height: 100%;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    background-color: rgba(108, 121, 136, 0.247);
    z-index: 1000;

}
</style>

<div id="spinner">
<div class="loader" id="loader"></div>
</div>