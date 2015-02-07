# cpe409-final-project

Milestone 1
==================

Vision and Scope
------------------

### Overview
Twiddit makes it easier to follow users’ posts on reddit. A user can follow people that post on reddit and have an easy way to view their posts and comments. Twiddit also allows users to make comments on posts and send private messages to other users. In addition, users can save their favorite posts and specify certain subreddits to see. Users of Twiddit are basically anyone who uses reddit. 
### 
### Features
Users can login to Twiddit through a reddit account or by creating an account on Twiddit. Twiddit allows users to add and delete other users from their list of followed users. Twiddit users can view posts from thier followed users on a wall that can be found on their main page. If users are logged in to their reddit account, they can comment on those posts. In addition, they can send private messages to other users and specify specific subreddits to have a peek at the top posts from those subreddits. 

Twiddit will not allow users to create their own posts on subreddits, or allow users to up post or down post. 


Team
------------------
Product Owner: Chris Opperwall
Scrum Master: Stanley Tang
Team Member: William Ho
Team Member: Stephen Calabrese


Low-Fidelity Prototype
------------------
### Login

![Login](https://media.taiga.io/attachments/f/2/1/4/24578907865571f7515c2f4b06b76318f5c8913844bfc0a1c50cfe4adcad/screen-shot-2015-02-02-at-92818-pm.png)

### Main Page

![Main Page](https://media.taiga.io/attachments/6/5/b/5/16237c7fee998dae2d3b72d7346c612013c41b817aec031a6d288e35a2ee/screen-shot-2015-02-02-at-92837-pm.png "")

### Settings

![Settings](https://media.taiga.io/attachments/8/e/e/5/fc6d958a89e13d30f681ec6f639811eeb859c57cf2bfd2a7da6686806221/screen-shot-2015-02-02-at-92756-pm.png)

REST API
------------------
We will be primarily using the Reddit API and overlaying our own API on top for our website specific needs. For example, our login would use our own API, but the user could also link their Reddit account as well. The Reddit API would be necessary to authenticate the user and allow us to retrieve the redditors followed by our user. It will also be used to display the comments made by the redditors and allow our user to respond to them through our website which would call the Reddit API. We would have to use the Reddit API to retrieve the redditors followed, their comments, the threads they commented on, and subreddit information. We would also use the POST call to allow our users to reply and send private messages to redditors.

2.)
Backlog: https://tree.taiga.io/project/sccalabr-cpe409/backlog
Assigned Task: https://tree.taiga.io/project/sccalabr-cpe409/taskboard/milestone-1-3
