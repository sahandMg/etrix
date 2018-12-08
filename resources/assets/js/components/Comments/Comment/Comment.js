import React , {Component} from 'react';

class Comment extends Component {
    render() {
        return (
            <div>
                <hr/>
                <div className="flex-row space-between">
                    <h6>محمد وطن دوست</h6>
                    <span className="text-left"> <i class="far fa-clock"></i> <time datetime="2008-02-14 20:00">۳۰ فروردین ۱۳۹۷</time></span>
                </div>
                <p>این مطلب عالی بود</p>
            </div>
        )
    }
}

export default Comment;