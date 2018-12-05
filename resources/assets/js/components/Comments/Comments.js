import React , {Component} from 'react';
import AddComment from './AddComment/AddComment';
import Comment from './Comment/Comment';
import { connect } from 'react-redux';

class Comments extends Component {
    render() {
        let addComment = <p className="text-center">برای ارسال دیدگاه باید ثبت نام کرده باشید</p>;
        if(this.props.token) {
            addComment =  <AddComment/>;
        }
        return (
            <div className="container text-right">
                <h3 className="text-center">لیست نظرات</h3>
                <br/>
                {addComment}
                <Comment/>
                <Comment/>
                <Comment/>
            </div>
        )
    }
}

const mapStateToProps = state => {
    return {
        token: state.auth.token,
    };
};


export default  connect(mapStateToProps,null)(Comments);