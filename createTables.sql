create table users(
userName varchar(30) not null,
userPassword varchar(30) not null,
primary key(userName)
);

create table followingRedditors(
userName varchar(30) not null,
redditor varchar(30) not null,
primary key(userName, redditor),
foreign key(userName) references users(userName)
);

create table followingSubreddit(
userName varchar(30) not null,
subreddit varchar(30) not null,
primary key(userName, subreddit),
foreign key(userName) references users(userName)
);


