const logoutDialog = document.getElementById('logout-dialog');
const openLogoutDialog = document.getElementById('open-logout-dialog');

openLogoutDialog.addEventListener('click', () => {
    logoutDialog.showModal();
});
