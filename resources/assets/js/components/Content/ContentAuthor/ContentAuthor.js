import React, { Component } from 'react';
import {convertFromRaw, EditorState} from 'draft-js';
import UserProfile from '../../../assets/Images/userprofile.png';

class ContentAuthor extends Component {

    componentDidMount() {
    }

    render() {
        return (
            <div className="flex-row flex-center-align">
                <span><b> نویسنده : </b></span>
                <div className="margin-left-1 margin-right-1">
                  <img src={UserProfile} width="40" height="40" alt="slide first"/>
                </div>
                <span><b> {this.props.author}</b></span>
            </div>
        )
    }
};

export default ContentAuthor;

