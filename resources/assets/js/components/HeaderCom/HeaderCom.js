import React, { Component } from 'react';
import DesktopHeader from './DesktopHeader/DesktopHeader';
import ResponsiveHeader from './ResponsiveHeader/ResponsiveHeader';
import {connect} from 'react-redux';
import * as actions from '../../store/actions/index';

class HeaderCom extends Component {

    componentDidMount() {
        console.log("HeaderCom componentDidMount")
        this.props.getCategories();
    }
    render() {
        return (
            <header>
                <DesktopHeader/>
                <ResponsiveHeader/>
            </header>
        )
    }
};

const mapDispatchToProps = dispatch => {
    return {
        getCategories: () => dispatch(actions.getProductCategories()),
    };
};


export default connect(null, mapDispatchToProps)(HeaderCom);

