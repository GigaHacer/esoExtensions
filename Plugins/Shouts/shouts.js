var Shouts = {

    init: function() {
        getById("shoutForm").onsubmit = function onsubmit() {
            Shouts.addShout(getById("shoutContent").value);
            return false;
        };
        disable(getById("shoutSubmit"));
        getById("shoutContent").onkeyup = function onkeydown() {
            window[this.value && !this.placeholder ? "enable" : "disable"](getById("shoutSubmit"));
        };
    },

    addShout: function(content) {
        disable(getById("shoutSubmit"));
        Ajax.request({
            url: eso.baseURL + "ajax.php?controller=profile",
            post: "action=shout&memberTo=" + encodeURIComponent(this.member) + "&content=" + encodeURIComponent(content),
            success: function() {
                if (this.messages) {
                    enable(getById("shoutSubmit"));
                    return;
                } else disable(getById("shoutSubmit"));
                var div = document.createElement("div");
                div.innerHTML = this.result.html;
                div.id = "shout" + this.result.shoutId;
                div.style.overflow = "hidden";
                getById("shouts").insertBefore(div, getById("shouts").firstChild);
                Shouts.animateNewShout(div);
                getById("shoutContent").value = "";
                getById("shoutContent").blur();
                Messages.hideMessage("waitToReply");
            }
        });
    },

    deleteShout: function(shoutId) {
        if (!confirm(eso.language.confirmDeleteShout)) return;
        Ajax.request({
            url: eso.baseURL + "ajax.php?controller=profile",
            post: "action=deleteShout&shoutId=" + shoutId,
            success: function() {
                Shouts.animateDeleteShout(getById("shout" + shoutId));
            }
        });
    },

    animateNewShout: function(shout) {
        var overflowDiv = createOverflowDiv(shout);
        (overflowDiv.animation = new Animation(function(values, final) {
            overflowDiv.style.height = final ? "" : values[0] + "px";
            overflowDiv.style.opacity = final ? "" : values[1];
        }, {begin: [0, 0], end: [overflowDiv.offsetHeight, 1]})).start();
    },

    animateDeleteShout: function(shout) {
        shout.style.overflow = "hidden";
        (shout.animation = new Animation(function(opacity, final) {
            shout.style.opacity = opacity;
            if (opacity < .5) {
                (shout.animation = new Animation(function(height, final) {
                    shout.style.height = height + "px";
                    if (final) shout.parentNode.removeChild(shout);
                }, {begin: shout.offsetHeight, end: 0})).start();
            }
        }, {begin: 1, end: 0})).start();
    }

};
