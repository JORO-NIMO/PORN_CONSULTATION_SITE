<?php
require_once 'config/config.php';
require_once 'includes/TwitterClient.php';

$twitter = new TwitterClient();
$tweets = $twitter->getLatestTweets(15);

$pageTitle = 'Recovery Community Tweets';
require_once 'includes/header.php';
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <h1 class="text-center mb-5">Latest from the Recovery Community</h1>
            
            <?php if (empty($tweets)): ?>
                <div class="alert alert-info">
                    <h4 class="alert-heading">No tweets found</h4>
                    <p>The Twitter feed is currently empty. The system will automatically fetch new tweets soon.</p>
                </div>
            <?php else: ?>
                <div class="twitter-feed">
                    <?php foreach ($tweets as $tweet): ?>
                        <div class="card mb-4 shadow-sm">
                            <div class="card-body">
                                <div class="d-flex align-items-center mb-3">
                                    <?php if (!empty($tweet['user_avatar'])): ?>
                                        <img src="<?php echo str_replace('_normal', '_200x200', $tweet['user_avatar']); ?>" 
                                             alt="@<?php echo htmlspecialchars($tweet['user_name']); ?>" 
                                             class="rounded-circle me-3" width="50" height="50">
                                    <?php endif; ?>
                                    <div>
                                        <h5 class="mb-0"><?php echo htmlspecialchars($tweet['user_display_name']); ?></h5>
                                        <a href="https://twitter.com/<?php echo htmlspecialchars($tweet['user_name']); ?>" 
                                           target="_blank" class="text-muted text-decoration-none">
                                            @<?php echo htmlspecialchars($tweet['user_name']); ?>
                                        </a>
                                    </div>
                                </div>
                                
                                <p class="card-text mb-3"><?php echo $this->formatTweetText($tweet['text']); ?></p>
                                
                                <?php if (!empty($tweet['media_url'])): ?>
                                    <div class="mb-3">
                                        <img src="<?php echo htmlspecialchars($tweet['media_url']); ?>" 
                                             alt="Tweet media" class="img-fluid rounded">
                                    </div>
                                <?php endif; ?>
                                
                                <div class="d-flex justify-content-between align-items-center">
                                    <small class="text-muted">
                                        <?php echo date('M j, Y \a\t g:i a', strtotime($tweet['created_at'])); ?>
                                    </small>
                                    <div>
                                        <a href="<?php echo htmlspecialchars($tweet['url']); ?>" 
                                           target="_blank" class="btn btn-outline-primary btn-sm">
                                            <i class="fab fa-x-twitter me-1"></i> View on X
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?n
        </div>
    </div>
</div>

<style>
.twitter-feed {
    max-width: 600px;
    margin: 0 auto;
}
.tweet-text {
    white-space: pre-wrap;
    word-wrap: break-word;
}
.tweet-text a {
    color: #1da1f2;
    text-decoration: none;
}
.tweet-text a:hover {
    text-decoration: underline;
}
</style>

<?php require_once 'includes/footer.php'; ?>
