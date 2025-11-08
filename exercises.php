<?php
require_once __DIR__ . '/config/config.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Exercises - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
<?php include 'includes/header.php'; ?>
<section class="page-hero">
    <div class="container">
        <h1>Exercises</h1>
        <p class="subtitle">Simple practices to relax and refocus</p>
    </div>
</section>
<main class="container exercises-page" style="padding:1.25rem 1rem;">
    <div class="content-grid">
        <div class="content-card" style="text-align:center;">
            <h3>Box Breathing</h3>
            <div id="breathCircle" style="width:160px;height:160px;border-radius:50%;margin:0 auto 1rem;background:radial-gradient(circle,#a5b4fc 0%,#6366f1 70%);transform:scale(1);transition:transform 1s ease-in-out;"></div>
            <div id="breathStep" style="margin-bottom:0.75rem;color:var(--muted);">Ready</div>
            <div style="display:flex;gap:.5rem;justify-content:center;">
                <button class="btn btn-primary" id="breathStart">Start</button>
                <button class="btn btn-secondary" id="breathStop">Stop</button>
            </div>
            <div style="margin-top:.75rem;font-size:.9rem;color:var(--muted);">Inhale • Hold • Exhale • Hold</div>
        </div>
        <div class="content-card">
            <h3>5-4-3-2-1 Grounding</h3>
            <ul style="padding-left:1.1rem;line-height:1.9;">
                <li>5 things you can see</li>
                <li>4 things you can feel</li>
                <li>3 things you can hear</li>
                <li>2 things you can smell</li>
                <li>1 thing you can taste</li>
            </ul>
            <button class="btn btn-secondary" onclick="alert('Take it slow and name each one aloud or in your mind')">Start</button>
        </div>
        <div class="content-card">
            <h3>Progressive Relaxation</h3>
            <ol style="padding-left:1.1rem;line-height:1.9;">
                <li>Feet and calves, tense 5 seconds, release 10 seconds</li>
                <li>Thighs and hips, tense 5 seconds, release 10 seconds</li>
                <li>Stomach and back, tense 5 seconds, release 10 seconds</li>
                <li>Hands and arms, tense 5 seconds, release 10 seconds</li>
                <li>Shoulders and neck, tense 5 seconds, release 10 seconds</li>
                <li>Face and jaw, tense 5 seconds, release 10 seconds</li>
            </ol>
        </div>
        <div class="content-card">
            <h3>Quick Stretch Reset</h3>
            <ol style="padding-left:1.1rem;line-height:1.9;">
                <li>Neck: gentle side tilts, 5 each side</li>
                <li>Shoulders: rolls forward and back, 10 each</li>
                <li>Arms: reach up and hold 10 seconds</li>
                <li>Back: slow twist left and right</li>
                <li>Breath: 3 slow deep breaths</li>
            </ol>
        </div>
    </div>
</main>
<?php include 'includes/footer.php'; ?>
<script>
(function(){
 let timer=null, phase=0, steps=['Inhale','Hold','Exhale','Hold'];
 let circle=document.getElementById('breathCircle');
 let label=document.getElementById('breathStep');
 function tick(){
   label.textContent=steps[phase];
   if(phase===0){ circle.style.transform='scale(1.1)'; }
   if(phase===2){ circle.style.transform='scale(0.9)'; }
   if(phase===1||phase===3){ circle.style.transform='scale(1)'; }
   phase=(phase+1)%4;
   timer=setTimeout(tick,4000);
 }
 document.getElementById('breathStart').onclick=function(){ if(timer) return; phase=0; tick(); };
 document.getElementById('breathStop').onclick=function(){ if(timer){ clearTimeout(timer); timer=null; } label.textContent='Ready'; circle.style.transform='scale(1)'; };
})();
</script>
</body>
</html>
