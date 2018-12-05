import React , {Component} from 'react';
import './AddComment.css';

class AddComment extends Component {
    render() {
        return (
            <div className="form-group">
                <label htmlFor="comment">دیدگاه شما</label>
                <textarea className="form-control" rows="3" id="comment"></textarea>
                <br/>
                <button className="btn btn-primary">ارسال دیدگاه</button>
            </div>
        )
    }
}

export default AddComment;