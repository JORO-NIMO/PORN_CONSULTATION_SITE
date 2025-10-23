<?php
require_once 'config/config.php';
requireLogin();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Discussions - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
<?php include 'includes/header.php'; ?>
<section class="page-hero">
    <div class="container">
        <h1>Discussions</h1>
        <p class="subtitle">Share insights and support</p>
    </div>
</section>
<main class="container discussions-page" style="padding:1.25rem 1rem;">
    <div style="max-width:720px;margin:0 auto;">
        <form id="newTopic" method="POST" style="display:grid;gap:.5rem;margin-bottom:1rem;">
            <input type="text" id="topicTitle" placeholder="Topic title" required style="padding:.75rem;">
            <textarea id="topicBody" rows="3" placeholder="What would you like to discuss?" required style="padding:.75rem;"></textarea>
            <button class="btn btn-primary" type="submit">Post Topic</button>
        </form>
        <div id="topics" class="content-list"></div>
    </div>
</main>
<?php include 'includes/footer.php'; ?>
<script>
(function(){
 const key='mfp_topics';
 const list=document.getElementById('topics');
 function sanitize(s){ const d=document.createElement('div'); d.textContent=s; return d.innerHTML; }
 function load(){
   list.innerHTML='';
   let items=[]; try{ items=JSON.parse(localStorage.getItem(key)||'[]'); }catch(e){ items=[]; }
   items.reverse().forEach(it=>{
     const card=document.createElement('div');
     card.className='content-list-item';
     card.innerHTML='<div class="content-info"><h3>'+sanitize(it.title)+'</h3><p>'+sanitize(it.body)+'</p><div style="font-size:.85rem;color:var(--muted);">'+new Date(it.ts).toLocaleString()+'</div></div>';
     list.appendChild(card);
   });
 }
 document.getElementById('newTopic').addEventListener('submit',function(e){
   e.preventDefault();
   const title=document.getElementById('topicTitle').value.trim();
   const body=document.getElementById('topicBody').value.trim();
   if(!title||!body) return;
   let items=[]; try{ items=JSON.parse(localStorage.getItem(key)||'[]'); }catch(e){ items=[]; }
   items.push({title,body,ts:Date.now(),user: <?php echo json_encode($_SESSION['user_id'] ?? null); ?>});
   localStorage.setItem(key, JSON.stringify(items));
   this.reset();
   load();
 });
 load();
})();
</script>
</body>
</html>
