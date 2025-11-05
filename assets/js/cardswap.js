// CardSwap (vanilla JS)
// Usage:
// <div class="cardswap" data-card-distance="60" data-vertical-distance="70" data-delay="5000" data-pause-on-hover="false">
//   <div class="cs-card"> ... </div>
//   <div class="cs-card"> ... </div>
// </div>
(function(){
  function positionCards(container) {
    var cardDistance = parseInt(container.dataset.cardDistance || '60', 10);
    var verticalDistance = parseInt(container.dataset.verticalDistance || '70', 10);
    var cards = Array.from(container.querySelectorAll('.cs-card'));
    var n = cards.length;
    cards.forEach(function(card, i){
      var x = i * cardDistance;
      var y = i * verticalDistance;
      var scale = Math.max(0.85, 1 - i * 0.05);
      var opacity = Math.max(0.4, 1 - i * 0.15);
      card.style.transform = 'translate(' + x + 'px,' + y + 'px) scale(' + scale + ')';
      card.style.opacity = opacity;
      card.style.zIndex = String(1000 - i);
    });
  }

  function startRotation(container) {
    var delay = parseInt(container.dataset.delay || '5000', 10);
    var pauseOnHover = (container.dataset.pauseOnHover || 'false') === 'true';
    var timerId = null;

    function tick(){
      var first = container.querySelector('.cs-card');
      if (!first) return;
      container.appendChild(first);
      positionCards(container);
    }

    function start(){
      if (timerId) clearInterval(timerId);
      timerId = setInterval(tick, delay);
    }

    function stop(){
      if (timerId) { clearInterval(timerId); timerId = null; }
    }

    if (pauseOnHover) {
      container.addEventListener('mouseenter', stop);
      container.addEventListener('mouseleave', start);
    }

    start();
  }

  function init(){
    document.querySelectorAll('.cardswap').forEach(function(container){
      // Ensure absolute positioning of cards
      positionCards(container);
      startRotation(container);
    });
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }
})();