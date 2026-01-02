// BCS - runtime configuration
// Default: API on https://api.<basedomain>
(function(){
  function baseDomain(host){
    return host.replace(/^app\./,'').replace(/^admin\./,'').replace(/^www\./,'');
  }
  const params = new URLSearchParams(window.location.search);
  const override = params.get('api') || localStorage.getItem('BCS_API_BASE');
  const bd = baseDomain(window.location.hostname);
  window.BCS_CONFIG = {
    API_BASE: override || (`https://api.${bd}`),
  };
})();
