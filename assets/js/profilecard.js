// Simple tilt effect for ProfileCard
(function(){
  function isTouchDevice(){
    return ('ontouchstart' in window) || (navigator.maxTouchPoints > 0);
  }
  function initTilt(card){
    const enableTilt = card.getAttribute('data-enable-tilt') === 'true';
    const enableMobileTilt = card.getAttribute('data-enable-mobile-tilt') === 'true';
    if (!enableTilt) return;
    if (isTouchDevice() && !enableMobileTilt) return;
    const maxRotate = 8; // degrees
    const maxTranslate = 8; // px
    function onMove(e){
      const rect = card.getBoundingClientRect();
      const x = (e.clientX ?? (e.touches && e.touches[0].clientX)) - rect.left;
      const y = (e.clientY ?? (e.touches && e.touches[0].clientY)) - rect.top;
      const cx = rect.width / 2;
      const cy = rect.height / 2;
      const rx = ((y - cy) / cy) * -maxRotate;
      const ry = ((x - cx) / cx) * maxRotate;
      const tx = ((x - cx) / cx) * maxTranslate;
      const ty = ((y - cy) / cy) * maxTranslate;
      card.style.transform = `rotateX(${rx}deg) rotateY(${ry}deg) translate(${tx}px, ${ty}px)`;
    }
    function reset(){ card.style.transform = 'translate(0,0)'; }
    card.addEventListener('mousemove', onMove);
    card.addEventListener('mouseleave', reset);
    card.addEventListener('touchmove', onMove, {passive: true});
    card.addEventListener('touchend', reset);
  }
  document.addEventListener('DOMContentLoaded', function(){
    document.querySelectorAll('.profile-card').forEach(initTilt);
  });
})();