insert into users values(USERNAME, USERPASSWORD);
insert into followingRedditors values(USERNAME, REDDITOR);
insert into followingSubreddit values(USERNAME, SUBREDDIT);

select redditor 
from users join followingRedditors 
where users.userName = followingRedditors.userName;

select subreddit
from users join followingSubreddit
where users.userName = followingRedditors.userName;