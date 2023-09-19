let allForums = [];
let allPosts = [];

const urlForumStr = `http://91.123.202.69:8000/forum`;
const urlForumPostsStr = `http://91.123.202.69:8000/post/forum/`;

function retrievePosts(forumId) {
  const url = `${urlForumPostsStr}${forumId}`;
  return fetch(url)
    .then((response) => response.json())
    .then((data) => {
      allPosts = data;
      console.log("All posts:", allPosts);
      return data; // Return the data array
    })
    .catch((error) => {
      console.error("Error retrieving posts:", error);
      throw error; // Rethrow the error to propagate it
    });
}

function retrieveForums() {
  return fetch(urlForumStr)
    .then((response) => response.json())
    .then((data) => {
      allForums = Object.values(omitToken(data)); // Convert object to array using Object.values()

      let forumListHTML = "";
      allForums.forEach((forum) => {
        forumListHTML += `<div class="forum forum-buttons" onclick="clickForum('${forum.id}', '${forum.name}')">${forum.name}</div>`;
      });
      document.getElementById("forumList").innerHTML = forumListHTML;
    })
    .catch((error) => {
      console.error("Error retrieving forums:", error);
    });
}

function clickForum(forumId, forumName) {
  const backButtonHTML = `<button class="back-button" onclick="showForumList()"></button>`;
  const headerHTML = `<h3>${forumName} Posts</h3>`;
  const forumNameContainerHTML = `<div class="forum-name-container">${backButtonHTML}${forumName}</div>`;
  let postsHTML = '';

  retrievePosts(forumId)
    .then((data) => {
      const forumPosts = Object.values(omitToken(data)); // Convert object to array using Object.values()


        forumPosts.forEach((post, index) => {
        const postNumber = forumPosts.length - index; // Calculate the post number based on index
        const [date, time] = post.created.split(' ');
        const authorName = post.name ? post.name : "&lt;deleted&gt;"; // Set user name to deleted if null
        postsHTML += `<div class="post">
                <div class="post-content">
                  <div class="post-info">
                      <div class="post-number">${postNumber}</div>
                      <span class="post-author">${authorName}</span>
                      <br>
                      <span class="post-time">${date}<br>${time}</span>
                  </div>
                  <p class="post-message">${post.message}</p>
                </div>
              </div>`;
      });

      document.getElementById("forumListContainer").style.display = "none";
      document.getElementById("forumPostsContainer").innerHTML = `
        <div class="forum-posts-header">
          ${forumNameContainerHTML}
        </div>
        <div class="forum-posts-content">
          ${postsHTML}
        </div>
      `;
    })
    .catch((error) => {
      console.error("Error retrieving forum posts:", error);
    });
}

function showForumList() {
  retrieveForums()
    .then(() => {
      document.getElementById("forumListContainer").style.display = "block";
      document.getElementById("forumListContainer").classList.add("forum-buttons");
      document.getElementById("forumPostsContainer").innerHTML = "";
    })
    .catch((error) => {
      console.error("Error retrieving forums:", error);
    });
}

function clickHandle(evt, tabName) {
  let i, tabcontent, tablinks;

  tabcontent = document.getElementsByClassName("tabcontent");
  for (i = 0; i < tabcontent.length; i++) {
    tabcontent[i].style.display = "none";
  }

  tablinks = document.getElementsByClassName("tablinks");
  for (i = 0; i < tablinks.length; i++) {
    tablinks[i].className = tablinks[i].className.replace(" active", "");
  }

  if (tabName === "main") {
    document.getElementById(tabName).style.display = "block";
  } else {
    document.getElementById(tabName).style.display = "block";
    evt.currentTarget.className += " active";
    if (tabName === "Forum") {
      showForumList();
    }
  }
}

function fetchTaskDescription() {
  fetch("html/task-description.html")
    .then((response) => response.text())
    .then((html) => {
      document.getElementById("main").innerHTML = html;
    })
    .catch((error) => {
      console.error("Error fetching task description html:", error);
    });
}

function fetchForumHtml() {
  fetch("html/forum.html")
    .then((response) => response.text())
    .then((html) => {
      document.getElementById("Forum").innerHTML = html;
    })
    .catch((error) => {
      console.error("Error fetching forum html:", error);
    });
}

document.addEventListener("DOMContentLoaded", function() {
  fetchTaskDescription();
  fetchForumHtml();
});

function omitToken(obj) {
  const { token, ...rest } = obj;
  return rest;
}
